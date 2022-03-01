<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Internals\FieldsTable;
use Sotbit\Regions\Internals\RegionsTable;

require_once($_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_admin_after.php');

$bPublicMode = defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1;

Loc::loadMessages(__FILE__);
set_time_limit(0);

$CSITE_ID = IntVal($CSITE_ID);
$STEP = IntVal($STEP);

if ($STEP <= 0)
    $STEP = 1;
if ($_SERVER["REQUEST_METHOD"] == "POST" && mb_strlen($backButton) > 0)
    $STEP = $STEP - 2;
if ($_SERVER["REQUEST_METHOD"] == "POST" && mb_strlen($backButton2) > 0)
    $STEP = 1;

if ($_SERVER['REQUEST_METHOD'] == "POST" && $STEP > 1 && check_bitrix_sessid()) {
    if ($STEP > 1) {
        $CSITE_ID = $_POST['CSITE_ID'];
    }
    if ($STEP > 2) {
        $csvFile = new CCSVData();
        $csvFile->SetFieldsType($fields_type);

        $delimiter_r_char = "";
        switch ($delimiter_r)
        {
            case "TAB":
                $delimiter_r_char = "\t";
                break;
            case "ZPT":
                $delimiter_r_char = ",";
                break;
            case "SPS":
                $delimiter_r_char = " ";
                break;
            case "OTR":
                $delimiter_r_char = mb_substr($delimiter_other_r, 0, 1, LANG_CHARSET);
                break;
            case "TZP":
                $delimiter_r_char = ";";
                break;
        }

        switch ($delimiter_r_fields)
        {
            case "TAB":
                $delimiter_r_f_char = "\t";
                break;
            case "ZPT":
                $delimiter_r_f_char = ",";
                break;
            case "SPS":
                $delimiter_r_f_char = " ";
                break;
            case "OTR":
                $delimiter_r_f_char = mb_substr($delimiter_other_r_fields, 0, 1, LANG_CHARSET);
                break;
            case "TZP":
                $delimiter_r_f_char = ";";
                break;
        }

        $DATA_FILE_NAME = encodingFileName($DATA_FILE_NAME, true);
        $fp = fopen($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, "w");
        if(!is_resource($fp))
        {
            $strError .= GetMessage("IBLOCK_ADM_EXP_CANNOT_CREATE_FILE")."<br>";
            $DATA_FILE_NAME = "";
        }
        else
        {
            fclose($fp);
        }

        $numericSort = false;
        foreach ($field_num as $el) {
            if (!is_numeric($el) && !is_int($el)) {
                $numericSort = true;
                break;
            }
        }

        if ($numericSort == true) {
            $strError .= GetMessage(SotbitRegions::moduleId . '_EXPORT_SORT_NUMERIC')."<br>";
        }

        $usersFieldsArray = CUserTypeEntity::GetList(
            array(),
            array('ENTITY_ID' => 'SOTBIT_REGIONS' , 'FIELD_NAME' => $keys, 'LANG' => $lang)
        );
        $usersFields = array();
        $usersFieldsSettings = array();
        while ($userFields = $usersFieldsArray->Fetch()) {
            array_push($usersFields, $userFields['FIELD_NAME']);
            array_push($usersFieldsSettings, ['FIELD_NAME' => $userFields['FIELD_NAME'], 'MULTIPLE' => $userFields['MULTIPLE']]);
        }


        $num_rows_writed = 0;
        $needFields = $_POST['field_needed'];
        $typesFields = array();
        foreach ($needFields as $field) {
            if (!in_array($field, $usersFields)) {
                $typesFields['regionsTable'][] = $field;
            } else {
                $typesFields['fieldsTable'][] = $field;
            }
        }


        $contentFields = array();
        $contentFields['regionsContent'] = RegionsTable::getList()->fetchAll();
        $contentFields['fieldsContent'] = FieldsTable::getList()->fetchAll();
        $expRegions = array();
        $arResFields = array();
        foreach ($contentFields['regionsContent'] as $key => $region) {
            foreach ($contentFields['fieldsContent'] as $fields) {
                if ($fields['ID_REGION'] == $region['ID']) {
                    $contentFields['regionsContent'][$key][$fields['CODE']] = $fields;
                }
            }
            if (ToLower($region['SITE_ID'][0]) == ToLower($CSITE_ID)) {
                array_push($expRegions, $contentFields['regionsContent'][$key]);
            }
        }
        $arrToExp = array();
        foreach ($expRegions as $key => $elRegion) {
            $arrToExp[$key] = array();
            if (!isset($typesFields['fieldsTable'])) {
                $typesFields['fieldsTable'] = array();
            }
            if (!isset($typesFields['regionsTable'])) {
                $typesFields['regionsTable'] = array();
            }
            foreach (array_merge($typesFields['regionsTable'], $typesFields['fieldsTable']) as $code) {

                if (array_key_exists($code, $elRegion)) {
                    $arrToExp[$key] += [$code => $elRegion[$code]];

                }

            }
        }
        ExportFields($arResFields, $arrToExp, $delimiter_r_char, $delimiter_r_f_char, $field_num);
    }
}

function GetSiteDropDownListEx($strTypeName, $strIBlockName, $arFilter = false, $onChangeType = '', $onChangeIBlock = '', $strAddType = '', $strAddIBlock = '')
{
    $html = '';
    static $arTypes = array();
    static $arSites = array();

    if(!is_array($arFilter))
        $arFilter = array();
    if (!array_key_exists('MIN_PERMISSION',$arFilter) || trim($arFilter['MIN_PERMISSION']) == '')
        $arFilter["MIN_PERMISSION"] = "W";
    $filterId = md5(serialize($arFilter));

    if(!isset($arTypes[$filterId]))
    {
        $arTypes[$filterId] = array(0 => GetMessage(SotbitRegions::moduleId . "_EXPORT_CHOOSE_SITE_TYPE"));
        $rsCSites= CSite::GetList($by="sort", $order="desc");

        while($arSite= $rsCSites->Fetch())
        {
            $tmpSITE_ID = $arSite["ID"];
            $arSites[$filterId][$tmpSITE_ID][$arSite["ID"]] = $arSite["NAME"]." [".$arSite["ID"]."]";
        }

        $html .= '
		<script type="text/javascript">
		function OnType_'.$filterId.'_Changed(typeSelect, iblockSelectID)
		{
			var arIBlocks = '.CUtil::PhpToJSObject($arSites[$filterId]).';
			var iblockSelect = BX(iblockSelectID);
			if(!!iblockSelect)
			{
				for(var i=iblockSelect.length-1; i >= 0; i--)
					iblockSelect.remove(i);
				for(var j in arIBlocks[typeSelect.value])
				{
					var newOption = new Option(arIBlocks[typeSelect.value][j], j, false, false);
					iblockSelect.options.add(newOption);
				}
			}
		}
		</script>
		';
    }

    $CSITE_TYPE = false;
    $htmlTypeName = htmlspecialcharsbx($strTypeName);
    $htmlIBlockName = htmlspecialcharsbx($strIBlockName);
    $onChangeType = 'OnType_'.$filterId.'_Changed(this, \''.CUtil::JSEscape($strIBlockName).'\');'.$onChangeType.';';
    $html .= '<select name="'.$htmlTypeName.'" id="'.$htmlTypeName.'" onchange="'.htmlspecialcharsbx($onChangeType).'" '.$strAddType.'>'."\n";

    foreach($arSites[$filterId] as $key => $value)
    {
        if($CSITE_TYPE === false)
            $CSITE_TYPE = $key;
        $html .= '<option value="'.htmlspecialcharsbx($key).'"'.($CSITE_TYPE===$key? ' selected': '').'>'.htmlspecialcharsEx($value[$key]).'</option>'."\n";
    }

    $html .= "</select>\n";
    return $html;
}

function ExportFields(&$arResult, $expRegions, $delimiter , $delimiter_field, $field_num, $arTemp = array()) {
    global $csvFile, $DATA_FILE_NAME, $num_rows_writed;
    $arTempR = array();
    $fldName = false;
    $usersFieldsArray = CUserTypeEntity::GetList(
        array(),
        array('ENTITY_ID' => 'SOTBIT_REGIONS' , 'FIELD_NAME' => $keys, 'LANG' => $lang)
    );
    $usersFields = array();
    $usersFieldsSettings = array();
    while ($userFields = $usersFieldsArray->Fetch()) {
        array_push($usersFields, $userFields['FIELD_NAME']);
        array_push($usersFieldsSettings, ['FIELD_NAME' => $userFields['FIELD_NAME'], 'MULTIPLE' => $userFields['MULTIPLE']]);
    }
    foreach ($expRegions as $key => $region) {
        if (!$fldName) {
            if ($_POST['first_line_names'] == 'Y') {

                    foreach ($region as $field_name => $field_content) {
                        if (!in_array($field_name, $usersFields)) {
                            $arTempR[] = 'R_' . $field_name;
                        } else {
                            $arTempR[] = $field_name;
                        }
                    }

                $arTemp[] = $arTempR;
                $arTempR = array();
                $fldName = true;
            }
        }
        //$first_line_names
        /* Price names */
        $sales = array();
        $sale_list = CCatalogGroup::GetList(array("SORT" => "ASC"));
        while ($sale = $sale_list->Fetch()) {
            array_push($sales, $sale);
        }

        $stores = array();
        $store_list = CCatalogStore::GetList(array("SORT" => "ASC"));
        while ($store = $store_list->Fetch()) {
            array_push($stores, $store);
        }

        foreach ($region as $field_name => $field_content) {
            if (is_array($field_content)) {
                if (unserialize($region[$field_name]["VALUE"]) === false) {
                    if ($field_name == 'PRICE_CODE' || $field_name == 'STORE') {
                        /* Price names */
                        if ($field_name == 'PRICE_CODE') {
                            foreach($sales as $sale) {
                                if ($sale['NAME'] ==  $region[$field_name][0]) {
                                    $arTempR[] = $sale['NAME_LANG'];
                                }
                            }
                        } else if ($field_name == 'STORE') {
                            foreach($stores as $store) {
                                if ($store['ID'] ==  $region[$field_name][0]) {
                                    $arTempR[] = $store['TITLE'];
                                }
                            }
                        }
                        else {
                            $arTempR[] = $region[$field_name][0];
                        }
                    } else {
                        $arTempR[] = $region[$field_name]["VALUE"];
                    }

                } else {
                    $str_field = '';
                    foreach (unserialize($region[$field_name]["VALUE"]) as $value) {
                        $str_field .= $value . $delimiter_field; //
                    }
                    $arTempR[] = $str_field;
                }
            } else {
                if (unserialize($field_content) === false) {
                    $arTempR[] = $field_content;
                } else {
                    $str_field = '';
                    foreach (unserialize($field_content) as $value) {
                        $str_field .= $value . $delimiter_field;
                    }
                    $arTempR[] = $str_field;
                }
            }
        }
        $arTemp[] = $arTempR;
        $arTempR = array();
        $num_rows_writed++;
    }

    asort($field_num);
    $arSortTemp = array();
    $i = 0;
    for (;$i < count($arTemp); $i++) {
        $arSortTemp[$i] = array();
        foreach ($field_num as $key => $nums) {
            array_push($arSortTemp[$i], $arTemp[$i][$key]);
        }
    }

    if (ToLower(SITE_CHARSET) == 'utf-8') {
        $encodeArray = array();
        foreach ($arSortTemp as $key => $item) {
            $encodeArray[$key] = array();
            foreach ($item as $itCount => $itElem) {
                array_push($encodeArray[$key], iconv('utf-8', 'cp1251', $itElem));
            }
        }
        foreach ($encodeArray as $item) {
            $csvFile->SetDelimiter($delimiter);
            $csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $item);
        }

    } else {
        foreach ($arSortTemp as $item) {
            $csvFile->SetDelimiter($delimiter);
            $csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $item);
        }
    }
}
$APPLICATION->SetTitle(Loc::getMessage(SotbitRegions::moduleId .'_EXPORT_NAME'));
?>

    <form method="POST" action="<?=$APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID; ?>" ENCTYPE="multipart/form-data" name="dataload">
        <input type="hidden" name="STEP" value="<?echo $STEP + 1;?>">
        <?=bitrix_sessid_post()?>
        <?
        if ($STEP > 1)
        {
            ?><input type="hidden" name="CSITE_ID" value="<?echo $CSITE_ID ?>"><?
        }
        if (!$bPublicMode)
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_TAB1"), "ICON" => "iblock", "TITLE" => GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_TAB1_ALT")),
                array("DIV" => "edit2", "TAB" => GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_TAB2"), "ICON" => "iblock", "TITLE" => GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_TAB2_ALT")),
                array("DIV" => "edit3", "TAB" => GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_TAB3"), "ICON" => "iblock", "TITLE" => GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_TAB3_ALT")),
            );
        else
            $aTabs = array(
                array("DIV" => "edit2", "TAB" => GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_TAB2"), "ICON" => "iblock", "TITLE" => GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_TAB2_ALT"))
            );

        $tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
        $tabControl->Begin();

        if (!$bPublicMode)
        {
            $tabControl->BeginNextTab();

            if ($STEP < 2)
            {
                ?>
                <tr>
                    <td width="40%"><?echo GetMessage(SotbitRegions::moduleId ."_EXPORT_ADM_EXP_CHOOSE_IBLOCK") ?></td>
                    <td width="60%">
                        <?echo GetSiteDropDownListEx(
                            'CSITE_ID',
                            'IBLOCK_ID',
                            array(
                                "MIN_PERMISSION" => "X",
                                "OPERATION" => "iblock_export",
                            ),
                            '',
                            '',
                            'class="adm-detail-iblock-types"',
                            'class="adm-detail-iblock-list"'
                        );?>
                    </td>
                </tr>
                <?
            }

            $tabControl->EndTab();
        }

        $tabControl->BeginNextTab();

        if ($STEP == 2)
        {
            ?>
            <tr class="heading">
                <td colspan="2">
                    <?echo GetMessage(SotbitRegions::moduleId .  "_EXPORT_ADM_EXP_CHOOSE_FORMAT") ?>
                    <input type="hidden" name="fields_type" value="R">
                    <input type="hidden" name="CSITE_ID" value="<?echo $CSITE_ID ?>">
                </td>
            </tr>
            <tr>
                <td width="40%" class="adm-detail-valign-top"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIMITER") ?>:</td>
                <td width="60%">
                    <input type="radio" name="delimiter_r" id="delimiter_TZP" value="TZP" <?if ($delimiter_r=="TZP" || mb_strlen($delimiter_r)<=0) echo "checked"?>><label for="delimiter_TZP"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_TZP") ?></label><br>
                    <input type="radio" name="delimiter_r" id="delimiter_ZPT" value="ZPT" <?if ($delimiter_r=="ZPT") echo "checked"?>><label for="delimiter_ZPT"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_ZPT") ?></label><br>
                    <input type="radio" name="delimiter_r" id="delimiter_TAB" value="TAB" <?if ($delimiter_r=="TAB") echo "checked"?>><label for="delimiter_TAB"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_TAB") ?></label><br>
                    <input type="radio" name="delimiter_r" id="delimiter_SPS" value="SPS" <?if ($delimiter_r=="SPS") echo "checked"?>><label for="delimiter_SPS"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_SPS") ?></label><br>
                    <input type="radio" name="delimiter_r" id="delimiter_OTR" value="OTR" <?if ($delimiter_r=="OTR") echo "checked"?>><label for="delimiter_OTR"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_OTR") ?></label>
                    <input type="text" name="delimiter_other_r" size="3" value="<?echo htmlspecialcharsbx($delimiter_other_r) ?>">
                </td>
            </tr>

            <tr>
                <td><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_FIRST_LINE_NAMES") ?>:</td>
                <td>
                    <input type="checkbox" name="first_line_names" value="Y" <?if ($first_line_names=="Y" || mb_strlen($strError)<=0) echo "checked"?>>
                </td>
            </tr>

            <tr class="heading">
                <td colspan="2">
                    <?echo GetMessage(SotbitRegions::moduleId .  "_EXPORT_ADM_EXP_DELIMITER_FORMAT") ?>
                    <input type="hidden" name="fields_type" value="R">
                    <input type="hidden" name="CSITE_ID" value="<?echo $CSITE_ID ?>">
                </td>
            </tr>
            <tr>
                <td width="40%" class="adm-detail-valign-top"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_CHOOSE_DELIMITER") ?>:</td>
                <td width="60%">
                    <input type="radio" name="delimiter_r_fields" id="delimiter_TZPP" value="TZP" <?if ($delimiter_r_f=="TZP" || mb_strlen($delimiter_r_f)<=0) echo "checked"?>><label for="delimiter_TZPP"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_TZP") ?></label><br>
                    <input type="radio" name="delimiter_r_fields" id="delimiter_ZPTP" value="ZPT" <?if ($delimiter_r_f=="ZPT") echo "checked"?>><label for="delimiter_ZPTP"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_ZPT") ?></label><br>
                    <input type="radio" name="delimiter_r_fields" id="delimiter_TABP" value="TAB" <?if ($delimiter_r_f=="TAB") echo "checked"?>><label for="delimiter_TABP"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_TAB") ?></label><br>
                    <input type="radio" name="delimiter_r_fields" id="delimiter_SPSP" value="SPS" <?if ($delimiter_r_f=="SPS") echo "checked"?>><label for="delimiter_SPSP"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_SPS") ?></label><br>
                    <input type="radio" name="delimiter_r_fields" id="delimiter_OTRP" value="OTR" <?if ($delimiter_r_f=="OTR") echo "checked"?>><label for="delimiter_OTRP"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DELIM_OTR") ?></label>
                    <input type="text" name="delimiter_other_r_fields" size="3" value="<?echo htmlspecialcharsbx($delimiter_other_r_f) ?>">
                </td>
            </tr>



            <tr class="heading">
                <td colspan="2"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_FIELDS_MAPPING") ?></td>
            </tr>

            <tr>
                <td colspan="2">
                    <?
                    $query = FieldsTable::getList();
                    $regionsProperty = RegionsTable::getEntity()->getFields();

                    foreach ($regionsProperty as $key => $prop) {
                        if ($key != 'SITE_ID') {
                            $arAvailFields[] = ["value"=>$key, "name"=>$prop->getTitle() ." (R_" . $key . ")"];
                        }
                    }

                    $arAvailFieldsValues = array();
                    foreach ($arAvailFields as $arField) {
                        $arAvailFieldsValues[] = $arField["value"];
                    }

                    $dbRes = CUserTypeEntity::GetList(
                        array(),
                        array('ENTITY_ID' => 'SOTBIT_REGIONS' , 'FIELD_NAME' => $keys, 'LANG' => $lang)
                    );

                    $usersFieldsArray = CUserTypeEntity::GetList(
                        array(),
                        array('ENTITY_ID' => 'SOTBIT_REGIONS' , 'FIELD_NAME' => $keys, 'LANG' => $lang)
                    );

                    $usersFields = array();
                    $usersFieldsSettings = array();
                    while ($userFields = $usersFieldsArray->Fetch()) {
                        array_push($usersFields, $userFields['FIELD_NAME']);
                        array_push($usersFieldsSettings, ['FIELD_NAME' => $userFields['FIELD_NAME'], 'MULTIPLE' => $userFields['MULTIPLE']]);
                    }

                    while ( $record = $query->fetch() ) {
                        if (!in_array($record["CODE"], $arAvailFieldsValues)) {
                            while ($item = $dbRes->Fetch()) {
                                if ($item['FIELD_NAME'] == $record['CODE']) {

                                    if (in_array($record["CODE"], $usersFields)) {
                                        $arAvailFields[] = array(
                                            "value" => $record["CODE"],
                                            "name" => $item['EDIT_FORM_LABEL'] . ' (' . $record["CODE"] . ')',
                                            "userField" => "N"
                                        );
                                        array_push($arAvailFieldsValues, $record["CODE"]);
                                        break;
                                    } else {
                                        $arAvailFields[] = array(
                                            "value" => $record["CODE"],
                                            "name" => $item['EDIT_FORM_LABEL'] . ' (R_' . $record["CODE"] . ')',
                                            "userField" => "Y"
                                        );
                                        array_push($arAvailFieldsValues, $record["CODE"]);
                                        break;
                                    }

                                }
                            }

                        }
                    }
                    $intCountFields = count($arAvailFields);
                    $intCountChecked = 0;
                    $arCheckID = array();
                    for ($i = 0; $i < $intCountFields; $i++)
                    {
                        if ($field_needed[$i]=="Y" || (!isset($field_needed) && mb_strlen($strCSVError)<=0))
                        {
                            $arCheckID[] = $i;
                            $intCountChecked++;
                        }
                    }
                    ?>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="internal">
                        <tr class="heading">
                            <td style="text-align: left !important;"><input type="checkbox" name="field_needed_all" id="field_needed_all" value="Y" onclick="checkAll(this,<? echo $intCountFields; ?>);"<? echo ($intCountChecked == $intCountFields ? ' checked' : ''); ?>>&nbsp;<?echo GetMessage(SotbitRegions::moduleId . '_EXPORT_ADM_EXP_IS_FIELD_NEEDED') ?></td>
                            <td><?echo GetMessage(SotbitRegions::moduleId . '_EXPORT_ADM_EXP_FIELD_NAME') ?></td>
                            <td><?echo GetMessage(SotbitRegions::moduleId . '_EXPORT_ADM_EXP_FIELD_SORT') ?></td>
                        </tr>
                        <?
                        for ($i = 0; $i < $intCountFields; $i++)
                        {
                            ?>
                            <tr>
                                <td style="text-align: left !important;">

                                    <input type="checkbox" <? if($arAvailFields[$i]["value"] == 'NAME' || $arAvailFields[$i]["value"] == 'CODE' || $arAvailFields[$i]["value"] == 'ID') {?> disabled checked <? }?> name="field_needed[<?echo $i ?>]" id="field_needed_<? echo $i; ?>"<?/*if ($field_needed[$i]=="Y" || mb_strlen($strError)<=0) echo "checked"; */ if (in_array($i,$arCheckID)) echo "checked"; ?> value="<?echo $arAvailFields[$i]["value"] ?>" onclick="checkOne(this,<? echo $intCountFields; ?>);">
                                    <? if($arAvailFields[$i]["value"] == 'NAME' || $arAvailFields[$i]["value"] == 'CODE' || $arAvailFields[$i]["value"] == 'ID') {?> <input type="hidden" style="display: none;" name="field_needed[<?echo $i ?>]" id="field_needed_<? echo $i; ?>"<?/*if ($field_needed[$i]=="Y" || mb_strlen($strError)<=0) echo "checked"; */ if (in_array($i,$arCheckID)) echo "checked"; ?> value="<?echo $arAvailFields[$i]["value"] ?>" onclick="checkOne(this,<? echo $intCountFields; ?>);"> <?}?>


                                </td>
                                <td>
                                    <?if ($i < 2) echo "<b>";?>
                                    <?echo $arAvailFields[$i]["name"]?>
                                    <?if ($i < 2) echo "</b>";?>
                                </td>
                                <td align="center">
                                    <?if ($i < 2) echo "<b>";?>
                                    <input type="text" name="field_num[<?echo $i ?>]" value="<?echo (is_array($field_num)?$field_num[$i]:(10*($i+1))) ?>" size="2">
                                    <input type="hidden" name="field_code[<?echo $i ?>]" value="<?echo $arAvailFields[$i]["value"] ?>">
                                    <?if ($i < 2) echo "</b>";?>
                                </td>
                            </tr>
                            <?
                        }
                        ?>
                    </table>
                    <input type="hidden" name="count_checked" id="count_checked" value="<? echo $intCountChecked; ?>">
                    <script type="text/javascript">
                        function checkAll(obj,cnt)
                        {
                            var boolCheck = obj.checked;
                            for (i = 0; i < cnt; i++)
                            {
                                if (!BX('field_needed_'+i).disabled) {
                                    BX('field_needed_'+i).checked = boolCheck;
                                }
                            }
                            BX('count_checked').value = (boolCheck ? cnt : 0);
                        }
                        function checkOne(obj,cnt)
                        {
                            var boolCheck = obj.checked;
                            var intCurrent = parseInt(BX('count_checked').value);
                            intCurrent += (boolCheck ? 1 : -1);
                            BX('field_needed_all').checked = (intCurrent < cnt ? false : true);
                            BX('count_checked').value = intCurrent;
                        }
                    </script>
                    <br><br>
                </td>
            </tr>

            <tr class="heading">
                <td colspan="2"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_FILE_NAME") ?></td>
            </tr>
            <tr>
                <td><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_ENTER_FILE_NAME") ?>:</td>
                <td><?
                    if (mb_strlen($DATA_FILE_NAME) > 0)
                    {
                        $exportFileName = $DATA_FILE_NAME;
                    }
                    else
                    {
                        $exportFileName = "/".COption::GetOptionString("main", "upload_dir", "upload")."/export_file_";
                        $exportFileName .= randString(16);
                        $exportFileName .= '.csv';
                    }
                    ?>
                    <input type="text" name="DATA_FILE_NAME" size="40" value="<?=htmlspecialcharsbx($exportFileName); ?>"><br>
                    <small><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_FILE_WARNING") ?></small>
                </td>
            </tr>
            <?
        }

        $tabControl->EndTab();

        if (!$bPublicMode)
        {
            $tabControl->BeginNextTab();
            if ($STEP > 2)
            {
                $viewFile = htmlspecialcharsbx(encodingFileName($DATA_FILE_NAME));
                ?>
                <tr>
                    <td>
                        <?echo CAdminMessage::ShowMessage(array(
                            "TYPE" => "PROGRESS",
                            "MESSAGE" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_SUCCESS"),
                            "DETAILS" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_LINES_EXPORTED", array("#LINES#" => "<b>".intval($num_rows_writed)."</b>")).'<br>'.GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_DOWNLOAD_RESULT", array("#HREF#" => "<a href=\"".$viewFile."\" target=\"_blank\">".$viewFile."</a>")),
                            "HTML" => true,
                        ))?>
                    </td>
                </tr>
                <?
            }
            $tabControl->EndTab();
        }

        if ($bPublicMode):
            $tabControl->Buttons(array());
        else:
            $tabControl->Buttons();
            if ($STEP < 3):
                if ($STEP > 1):
                    ?>
                    <input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_BACK_BUTTON") ?>">
                <?
                endif;
                ?>
                <input type="submit" value="<?echo ($STEP==2)?GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_FINISH_BUTTON"):GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_NEXT_BUTTON") ?> &gt;&gt;" name="submit_btn" class="adm-btn-save">
            <?
            else:
                ?>
                <input type="submit" name="backButton2" value="&lt;&lt; <?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_RESTART_BUTTON") ?>" class="adm-btn-save">
            <?
            endif;
        endif;

        $tabControl->End();
        if (!$bPublicMode):
            ?>
            <script type="text/javaScript">
                <!--
                BX.ready(function() {
                    <?if ($STEP < 2):?>
                    tabControl.SelectTab("edit1");
                    tabControl.DisableTab("edit2");
                    tabControl.DisableTab("edit3");
                    <?elseif ($STEP == 2):?>
                    tabControl.SelectTab("edit2");
                    tabControl.DisableTab("edit1");
                    tabControl.DisableTab("edit3");
                    <?elseif ($STEP > 2):?>
                    tabControl.SelectTab("edit3");
                    tabControl.DisableTab("edit1");
                    tabControl.DisableTab("edit2");
                    <?endif;?>
                });
                //-->
            </script>
        <?
        endif;
        ?>
    </form>

<?

function encodingFileName($string, $save = false) {
    $utf = "utf-8";
    if(ToLower(SITE_CHARSET) != $utf) {
        $string = mb_convert_encoding($string, ($save ? $utf : SITE_CHARSET), ($save ? SITE_CHARSET : $utf));
    }
    return $string;
}

require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");

