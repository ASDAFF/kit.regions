<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Internals\FieldsTable;
use Sotbit\Regions\Internals\RegionsTable;

require_once($_SERVER['DOCUMENT_ROOT']
    .'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT']
    .'/bitrix/modules/main/include/prolog_admin_after.php');

Loc::loadMessages(__FILE__);


set_time_limit(0);
$IBLOCK_ID = intval($IBLOCK_ID);
$STEP = intval($STEP);


if ($STEP <= 0)
    $STEP = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["backButton"]) && mb_strlen($_POST["backButton"]) > 0)
    $STEP = $STEP - 2;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["backButton2"]) && mb_strlen($_POST["backButton2"]) > 0)
    $STEP = 1;

$NUM_CATALOG_LEVELS = (int)COption::GetOptionInt('iblock', 'num_catalog_levels');
if ($NUM_CATALOG_LEVELS <= 0)
    $NUM_CATALOG_LEVELS = 3;
$max_execution_time = intval($max_execution_time);
if ($max_execution_time <= 0)
    $max_execution_time = 0;

if (isset($_REQUEST["CUR_LOAD_SESS_ID"]) && mb_strlen($_REQUEST["CUR_LOAD_SESS_ID"]) > 0)
    $CUR_LOAD_SESS_ID = $_REQUEST["CUR_LOAD_SESS_ID"];
else
    $CUR_LOAD_SESS_ID = "CL".time();

$bAllLinesLoaded = True;
$CUR_FILE_POS = isset($_REQUEST["CUR_FILE_POS"]) ? intval($_REQUEST["CUR_FILE_POS"]) : 0;
$strError = "";
$line_num = 0;
$correct_lines = 0;
$error_lines = 0;
$killed_lines = 0;
$io = CBXVirtualIo::GetInstance();

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
        $arTypes[$filterId] = array(0 => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_SITE"));
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

/**
 * Remove Excess Items
 *
 * @param array Massive parameters
 * @return array Filtered massive
 */
function RemoveExcessItems($array) {
    $arFiltered = array();

    foreach ($array as $key => $item) {
        if (mb_strlen($item) > 1 && $item != "") {
            $arFiltered[] = $item;
        }
    }

    return $arFiltered;
}

class CAssocData extends CCSVData
{
    var $__rows = array();
    var $__pos = array();
    var $__last_pos = 0;
    var $NUM_FIELDS = 0;
    var $IBLOCK_ID = 0;
    var $tmpid = "";
    var $PK = array();
    var $GROUP_REGEX = "";

    function __construct($fields_type = "R", $first_header = false, $NUM_FIELDS = 0)
    {
        parent::__construct($fields_type, $first_header);
        $this->NUM_FIELDS = (int)$NUM_FIELDS;
    }



    function GetPos()
    {
        if(empty($this->__pos))
            return parent::GetPos();
        else
            return $this->__pos[count($this->__pos) - 1];
    }

    function Fetch()
    {
        if (empty($this->__rows))
        {
            $this->__last_pos = $this->GetPos();
            return parent::Fetch();
        }
        else
        {
            $this->__last_pos = array_pop($this->__pos);
            return array_pop($this->__rows);
        }
    }

    function PutBack($row)
    {
        $this->__rows[] = $row;
        $this->__pos[] = $this->__last_pos;
    }

    function AddPrimaryKey($field_name, $field_ind)
    {
        $this->PK[$field_name] = $field_ind;
    }

    function SetGroupFields($arGroupFields)
    {
        $ar = array();
        foreach ($arGroupFields as $name => $arField)
            $ar[] = $name;

        $this->GROUP_REGEX = "/^(".implode("|", $ar).")\\d+\$/";
    }

    function FetchAssoc()
    {
        global $line_num;
        $result = array();
        while ($ar = $this->Fetch())
        {
            $line_num++;
            //Search for "PRIMARY KEY"
            foreach ($this->PK as $pk_field => $pk_ind)
            {
                if (array_key_exists($pk_field, $result))
                {
                    //Check for Next record
                    if ($result[$pk_field] !== "".trim($ar[$pk_ind]))
                    {
                        $line_num--;
                        $this->PutBack($ar);
                        return $result;
                    }
                    else
                    {
                        //When XML_ID do match we skip NAME check
                        break;
                    }
                }
            }
            for ($i = 0; $i < $this->NUM_FIELDS; $i++)
            {
                $key = $GLOBALS["field_".$i];
                $value = "".trim($ar[$i]);
                if (preg_match($this->GROUP_REGEX, $key))
                {
                    //IBlockSection
                    if (!array_key_exists($key, $result))
                        $result[$key] = array();

                    $result[$key][] = $value;
                }
                elseif (preg_match("/^IP_PROP/", $key))
                {
                    //Multiple values only for properties
                    if (!array_key_exists($key, $result))
                    {
                        $result[$key] = $value;
                    }
                    elseif (!is_array($result[$key]) && $result[$key] !== $value)
                    {
                        $result[$key] = array(
                            $result[$key],
                        );
                        $result[$key][] = $value;
                    }
                    elseif (is_array($result[$key]) && !in_array($value, $result[$key]))
                    {
                        $result[$key][] = $value;
                    }
                    else
                    {
                        //we ignore repeated values
                    }
                }
                else
                {
                    $result[$key] = $value;
                }
            }
            if (empty($this->PK))
                return $result;
        }
        //eof
        if (empty($result))
            return $ar;
        else
            return $result;
    }

    function MapSections($arRes)
    {
        global $NUM_CATALOG_LEVELS, $arIBlockAvailGroupFields;
        static $arSectionCache = array();
        $bs = new CIBlockSection;
        $result = array();
        while (true)
        {
            $stop_processing = false;
            // this array is path to element
            $arGroupsTmp = array();
            for ($i = 0; $i < $NUM_CATALOG_LEVELS; $i++)
            {
                $bOK = false; //will be true when at least one important field met
                $arGroupsTmp1 = array(
                    "TMP_ID" => $this->tmpid,
                );
                foreach ($arIBlockAvailGroupFields as $key => $value)
                {
                    $fkey = $value["field"];
                    if (array_key_exists($key.$i, $arRes) && !empty($arRes[$key.$i]))
                    {
                        $arGroupsTmp1[$fkey] = array_shift($arRes[$key.$i]);
                    }
                    if ($value["important"] == "Y" && isset($arGroupsTmp1[$fkey]) && mb_strlen($arGroupsTmp1[$fkey]) > 0)
                        $bOK = true;
                }
                // drop empty target sections
                if ($bOK)
                {
                    // When group does not have name  "<Empty name>"
                    if (mb_strlen($arGroupsTmp1["NAME"]) <= 0)
                        $arGroupsTmp1["NAME"] = GetMessage("IBLOCK_ADM_IMP_NOMAME");

                    if (!$stop_processing)
                    {
                        $arGroupsTmp[] = $arGroupsTmp1;
                    }
                }
                else
                {
                    $stop_processing = true;
                }
            }

            //Finished with groups
            if (empty($arGroupsTmp))
                break;

            // Create sections tree. Save section code for elemet insertions
            $LAST_GROUP_CODE = 0;
            foreach ($arGroupsTmp as $i => $arGroup)
            {
                $arFilter = array(
                    "IBLOCK_ID" => $this->IBLOCK_ID,
                    "CHECK_PERMISSIONS" => "N",
                );

                if (isset($arGroup["XML_ID"]) && mb_strlen($arGroup["XML_ID"]))
                {
                    $arFilter["=XML_ID"] = $arGroup["XML_ID"];
                }
                elseif (isset($arGroup["NAME"]) && mb_strlen($arGroup["NAME"]))
                {
                    $arFilter["=NAME"] = $arGroup["NAME"];
                }

                if ($LAST_GROUP_CODE > 0)
                {
                    $arFilter["SECTION_ID"] = $LAST_GROUP_CODE;
                    $arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = $LAST_GROUP_CODE;
                }
                else
                {
                    $arFilter["SECTION_ID"] = 0;
                    $arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = false;
                }

                $cache_id = md5(serialize($arFilter));
                if (array_key_exists($cache_id, $arSectionCache))
                {
                    $arr = $arSectionCache[$cache_id];
                }
                else
                {
                    $res = CIBlockSection::GetList(array() , $arFilter);
                    if ($arr = $res->Fetch())
                        $arSectionCache[$cache_id] = $arr;
                }

                if ($arr)
                {
                    $arGroupsTmp[$i]["IBLOCK_ID"] = $arr["IBLOCK_ID"];
                    $LAST_GROUP_CODE = $arr["ID"];
                    $bUpdate = false;
                    foreach ($arGroupsTmp[$i] as $field_code => $field_value)
                    {
                        if ($field_value."" !== $arr[$field_code]."")
                        {
                            $bUpdate = true;
                            break;
                        }
                    }
                    if ($bUpdate)
                    {
                        $res = $bs->Update($LAST_GROUP_CODE, $arGroupsTmp[$i]);
                        unset($arSectionCache[$cache_id]);
                    }
                }
                else
                {
                    $arGroupsTmp[$i]["IBLOCK_ID"] = $this->IBLOCK_ID;
                    $arGroupsTmp[$i]["ACTIVE"] = "Y";
                    $LAST_GROUP_CODE = $bs->Add($arGroupsTmp[$i]);
                }
            }
            if ($LAST_GROUP_CODE > 0)
                $result[$LAST_GROUP_CODE] = $LAST_GROUP_CODE;
        }
        return $result;
    }

    function MapEnum($prop_id, $value)
    {
        static $arEnumCache = array();
        if (is_array($value))
        {
            foreach ($value as $k => $v)
                $value[$k] = $this->MapEnum($prop_id, $v);
        }
        else
        {
            if (!isset($arEnumCache[$prop_id]))
                $arEnumCache[$prop_id] = array();

            if (array_key_exists($value, $arEnumCache[$prop_id]))
            {
                $value = $arEnumCache[$prop_id][$value];
            }
            else
            {
                $res2 = CIBlockProperty::GetPropertyEnum($prop_id, array() , array(
                    "IBLOCK_ID" => $this->IBLOCK_ID,
                    "VALUE" => $value,
                ));
                if ($arRes2 = $res2->Fetch())
                    $value = $arEnumCache[$prop_id][$value] = $arRes2["ID"];
                else
                    $value = $arEnumCache[$prop_id][$value] = CIBlockPropertyEnum::Add(array(
                        "PROPERTY_ID" => $prop_id,
                        "VALUE" => $value,
                        "TMP_ID" => $this->tmpid,
                    ));
            }
        }
        return $value;
    }

    function MapFiles($value)
    {
        global $PATH2PROP_FILES;
        $io = CBXVirtualIo::GetInstance();

        if (!is_array($value))
            $value = array(
                $value,
            );

        $result = array();
        $j = 0;
        foreach ($value as $i => $file_name)
        {
            if (mb_strlen($file_name) > 0)
            {
                if (preg_match("/^(ftp|ftps|http|https):\\/\\//", $file_name))
                    $arFile = CFile::MakeFileArray($file_name);
                else
                    $arFile = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2PROP_FILES."/".$file_name));

                if (isset($arFile["tmp_name"]))
                    $result["n".($j++)] = $arFile;
            }
        }
        return $result;
    }


}
/////////////////////////////////////////////////////////////////////
if (($_SERVER['REQUEST_METHOD'] == "POST" || $CUR_FILE_POS > 0) && $STEP > 1 && check_bitrix_sessid())
{
    //*****************************************************************//
    if ($STEP > 1)
    {
        //*****************************************************************//
        $DATA_FILE_NAME = "";
        if (isset($_FILES["DATA_FILE"]) && is_uploaded_file($_FILES["DATA_FILE"]["tmp_name"]))
        {
            if (mb_strtolower(GetFileExtension($_FILES["DATA_FILE"]["name"])) != "csv")
            {
                $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NOT_CSV")."<br>";
            }
            else
            {
                $DATA_FILE_NAME = "/".COption::GetOptionString("main", "upload_dir", "upload")."/".basename($_FILES["DATA_FILE"]["name"]);
                if ($APPLICATION->GetFileAccessPermission($DATA_FILE_NAME) >= "W")
                    copy($_FILES["DATA_FILE"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);
                else
                    $DATA_FILE_NAME = "";
            }
        }

        if (mb_strlen($strError) <= 0)
        {
            if (mb_strlen($DATA_FILE_NAME) <= 0)
            {
                if (mb_strlen($URL_DATA_FILE) > 0)
                {
                    $URL_DATA_FILE = trim(str_replace("\\", "/", trim($URL_DATA_FILE)) , "/");
                    $FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$URL_DATA_FILE);
                    if (
                        (mb_strlen($FILE_NAME) > 1)
                        && ($FILE_NAME === "/".$URL_DATA_FILE)
                        && $io->FileExists($_SERVER["DOCUMENT_ROOT"].$FILE_NAME)
                        && ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W")
                    )
                    {
                        $DATA_FILE_NAME = $FILE_NAME;
                    }

                }
            }

            if (mb_strlen($DATA_FILE_NAME) <= 0)
                $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NO_DATA_FILE_SIMPLE")."<br>";

            if (!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "element_edit_any_wf_status"))
                $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NO_IBLOCK")."<br>";
        }

        if (mb_strlen($strError) <= 0)
        {
            if ($CUR_FILE_POS > 0 && is_set($_SESSION, $CUR_LOAD_SESS_ID) && is_set($_SESSION[$CUR_LOAD_SESS_ID], "LOAD_SCHEME"))
            {
                parse_str($_SESSION[$CUR_LOAD_SESS_ID]["LOAD_SCHEME"]);
                $STEP = 4;
            }
        }

        if (mb_strlen($strError) > 0)
            $STEP = 1;
        //*****************************************************************//

    }
    if ($STEP > 2)
    {
        //*****************************************************************//
        $csvFile = new CAssocData;
        $csvFile->LoadFile($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME));
        $headersFile = new CAssocData;
        $headersFile->LoadFile($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME));

        $arDataFileFields = array();
        if (mb_strlen($strError) <= 0)
        {
            $fields_type = (($fields_type == "F") ? "F" : "R");
            $csvFile->SetFieldsType($fields_type);
            $headersFile->SetFieldsType($fields_type);
            if ($fields_type == "R")
            {
                $first_names_r = (($first_names_r == "Y") ? "Y" : "N");
                $csvFile->SetFirstHeader(($first_names_r == "Y") ? true : false);
                $headersFile->SetFirstHeader(($first_names_r == "Y") ? true : false);
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
                if (mb_strlen($delimiter_r_char) != 1)
                    $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NO_DELIMITER")."<br>";

                if (mb_strlen($strError) <= 0)
                {
                    $csvFile->SetDelimiter($delimiter_r_char);
                    $headersFile->SetDelimiter($delimiter_r_char);
                }
            }
            else
            {
                $first_names_f = (($first_names_f == "Y") ? "Y" : "N");
                $csvFile->SetFirstHeader(($first_names_f == "Y") ? true : false);
                $headersFile->SetFirstHeader(($first_names_f == "Y") ? true : false);
                if (mb_strlen($metki_f) <= 0)
                    $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NO_METKI")."<br>";

                if (mb_strlen($strError) <= 0)
                {
                    $arMetki = array();
                    foreach (preg_split("/[\D]/i", $metki_f) as $metka)
                    {
                        $metka = intval($metka);
                        if ($metka > 0)
                            $arMetki[] = $metka;
                    }

                    if (!is_array($arMetki) || count($arMetki) < 1)
                        $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NO_METKI")."<br>";

                    if (mb_strlen($strError) <= 0)
                    {
                        $csvFile->SetWidthMap($arMetki);
                        $headersFile->SetWidthMap($arMetki);
                    }
                }
            }


            if (mb_strlen($strError) <= 0)
            {
                if ($first_names_r == 'Y') {
                    $bFirstHeaderTmp = $csvFile->GetFirstHeader();
                    $csvFile->SetFirstHeader(false);
                    if ($arRes = $csvFile->Fetch())
                    {
                        foreach ($arRes as $i => $ar)
                        {
                            $arDataFileFields[$i] = $ar;
                        }
                    }
                    else
                    {
                        $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NO_DATA")."<br>";
                    }
                    $NUM_FIELDS = count($arDataFileFields);
                } else {
                    $bFirstHeaderTmp = $headersFile->GetFirstHeader();
                    $csvFile->SetFirstHeader(false);
                    if ($arRes = $headersFile->Fetch())
                    {
                        foreach ($arRes as $i => $ar)
                        {
                            $arDataFileFields[$i] = $ar;
                        }
                    }
                    else
                    {
                        $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NO_DATA")."<br>";
                    }
                    $NUM_FIELDS = count($arDataFileFields);
                }

            }
        }

        if (mb_strlen($strError) > 0)
            $STEP = 2;
        //*****************************************************************//

    }
    if ($STEP > 3)
    {
        //*****************************************************************//
        $bFieldsPres = False;
        for ($i = 0; $i < $NUM_FIELDS; $i++)
        {
            if (mb_strlen(${"field_".$i}) > 0)
            {
                $bFieldsPres = True;
                break;
            }
        }


        switch ($delimiter_r_f)
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
                $delimiter_r_f_char = mb_substr($delimiter_r_f_char, 0, 1, LANG_CHARSET);
                break;
            case "TZP":
                $delimiter_r_f_char = ";";
                break;
        }

        if (!$bFieldsPres)
            $strError.= GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NO_FIELDS")."<br>";

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

        if (mb_strlen($strError) <= 0) {

            if (ToLower(SITE_CHARSET) == 'utf-8') {
                while ($item = $csvFile->Fetch()) {
                    $itemEncoding = array();
                    foreach ($item as $itCount => $itElem) {
                        array_push($itemEncoding, iconv('cp1251', 'utf-8', $itElem));
                    }
                    $arFileData[] = $itemEncoding;
                }
            } else {
                while ($item = $csvFile->Fetch()) {
                    $arFileData[] = $item;
                }
            }


            if ($field_update == "") {
                $line_num = 0;
                $correct_lines = 0;

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

                $regionsQuery = RegionsTable::getList()->fetchAll();
                $fieldQuery = FieldsTable::getList()->fetchAll();
                $regionsCount = (int)$regionsQuery[count($regionsQuery)-1]['ID'];
                $fieldsCount = (int)$fieldQuery[count($fieldQuery)-1]['ID'];

                $arFieldsToImp = array();
                $arFieldsToImpS = array();
                foreach ($arFileData as $key => $item) {
                    $arFieldsToImp[$key] = array();
                    $arFieldsToImpS[$key] = array();
                    for ($i = 0; $i < $NUM_FIELDS; $i++)
                    {
                        if (${"field_".$i} == 'ID') {
                            $regionsCount += 1;
                            $arFieldsToImp[$key] += [(${"field_".$i}) => $regionsCount];
                        } else {
                            if (${"field_".$i} == 'PRICE_CODE' || ${"field_".$i} == 'STORE') {
                                if (${"field_".$i} == 'PRICE_CODE') {
                                    foreach($sales as $sale) {
                                        if ($sale['NAME_LANG'] ==  $item[$i]) {
                                            $arFieldsToImp[$key] += [(${"field_".$i}) => array(0 => $sale['NAME'])];
                                        }
                                    }
                                } else if (${"field_".$i} == 'STORE') {
                                    foreach($stores as $store) {
                                        if ($store['TITLE'] ==  $item[$i]) {
                                            $arFieldsToImp[$key] += [(${"field_".$i}) => array(0 => $store['ID'])];
                                        }
                                    }
                                } else {
                                    $arFieldsToImp[$key] += [(${"field_".$i}) => array(0 => $item[$i])];
                                }
                            } else {
                                $arFieldsToImp[$key] += [(${"field_".$i}) => $item[$i]];
                            }
                        }

                    }
                    $arFieldsToImp[$key] += ["SITE_ID" =>
                        array(
                            0 => $CSITE_ID
                        )
                    ];
                    $counter = 0;
                }
                foreach ($arFieldsToImp as $data_imp) {
                    RegionsTable::add($data_imp);
                    $line_num++;
                }

                $regionsQuery = RegionsTable::getList()->fetchAll();
                $fieldQuery = FieldsTable::getList()->fetchAll();
                $regionsCount = (int)$regionsQuery[count($regionsQuery)-1]['ID'];
                $fieldsCount = (int)$fieldQuery[count($fieldQuery)-1]['ID'];

                foreach ($arFileData as $key => $item) {
                    foreach ($arFieldsToImp[$key] as $type => $elem) {
                        if (in_array($type, $usersFields)) {
                            $fieldsCount += 1;
                            $arFieldsToImpS[$key][$counter]["ID"] = $fieldsCount;
                            $arFieldsToImpS[$key][$counter]['ID_REGION'] = $arFieldsToImp[$key]["ID"];
                            $arFieldsToImpS[$key][$counter]['CODE'] = $type;
                            $arFieldsToImpS[$key][$counter]['VALUE'] = explode($delimiter_r_f_char, $elem);
                            $counter++;
                        }
                    }
                    $arFieldsToImpR = array();
                    foreach ($arFieldsToImpS as $arItem) {
                        foreach ($arItem as $it) {
                            array_push($arFieldsToImpR, $it);
                        }
                    }
                }
                foreach ($arFieldsToImpR as $data_imp) {
                    foreach ($usersFieldsSettings as $fieldSetting) {
                        if ($fieldSetting['FIELD_NAME'] == $data_imp['CODE']) {
                            if ($fieldSetting['MULTIPLE'] == 'Y') {
                                $valToEpx = $data_imp['VALUE'];

                                $valToEpx = RemoveExcessItems($valToEpx);
//                                if (count($valToEpx[count($valToEpx)-1]) == "")
//                                    unset($valToEpx[count($valToEpx)-1]);
                                FieldsTable::add([
                                    'ID'        => $data_imp['ID'],
                                    'ID_REGION' => $data_imp['ID_REGION'],
                                    'CODE'      => $data_imp['CODE'],
                                    'VALUE'     => serialize($valToEpx),
                                ]);
                            } else {
                                FieldsTable::add([
                                    'ID'        => $data_imp['ID'],
                                    'ID_REGION' => $data_imp['ID_REGION'],
                                    'CODE'      => $data_imp['CODE'],
                                    'VALUE'     => $data_imp['VALUE'][0],
                                ]);
                            }

                        }
                    }

                }
            }
            else {
                $line_num = 0;
                $correct_lines = 0;

                $regionsQuery = RegionsTable::getList()->fetchAll();
                $fieldQuery = FieldsTable::getList()->fetchAll();
                $regionsCount = (int)$regionsQuery[count($regionsQuery)-1]['ID'];
                $fieldsCount = (int)$fieldQuery[count($fieldQuery)-1]['ID'];

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

                foreach ($arFileData as $key => $item) {
                    $arFieldsToImp[$key] = array();
                    $arFieldsToImpS[$key] = array();
                    for ($i = 0; $i < $NUM_FIELDS; $i++)
                    {
                        if (${"field_".$i} == 'ID') {
                            $regionsCount += 1;
                            $arFieldsToImp[$key] += [(${"field_".$i}) => $regionsCount];
                        } else {
                            if (${"field_".$i} == 'PRICE_CODE' || ${"field_".$i} == 'STORE') {
                                if (${"field_".$i} == 'PRICE_CODE') {
                                    foreach($sales as $sale) {
                                        if ($sale['NAME_LANG'] ==  $item[$i]) {
                                            $arFieldsToImp[$key] += [(${"field_".$i}) => array(0 => $sale['NAME'])];
                                        }
                                    }
                                } else if (${"field_".$i} == 'STORE') {
                                    foreach($stores as $store) {
                                        if ($store['TITLE'] ==  $item[$i]) {
                                            $arFieldsToImp[$key] += [(${"field_".$i}) => array(0 => $store['ID'])];
                                        }
                                    }
                                } else {
                                    $arFieldsToImp[$key] += [(${"field_".$i}) => array(0 => $item[$i])];
                                }
                            } else {
                                $arFieldsToImp[$key] += [(${"field_".$i}) => $item[$i]];
                            }
                        }

                    }
                    $arFieldsToImp[$key] += ["SITE_ID" =>
                        array(
                            0 => $CSITE_ID
                        )
                    ];
                    $counter = 0;
                }

                $arRegion = RegionsTable::getList()->fetchAll();
                $arField = FieldsTable::getList()->fetchAll();
                foreach ($arRegion as $key => $region) {
                    foreach ($arField as $field) {
                        if ($region['ID'] == $field['ID_REGION']) {
                            $arRegion[$key] += [$field['CODE'] => $field['VALUE']];
                        }
                    }
                }

                foreach ($arFieldsToImp as $impField) {
                    $ck_fld = false;
                    foreach ($arRegion as $allField) {
                        if (ToLower($allField['SITE_ID'][0]) == ToLower($CSITE_ID)) {

                            if ($impField[$field_update] == $allField[$field_update]) {
                                RegionsTable::update($allField['ID'], $impField);
                                $correct_lines++;
                                foreach ($impField as $namFld => $fld) {
                                    if (in_array($namFld, $usersFields)) {

                                        foreach ($arField as $field) {
                                            if ($field['ID_REGION'] == $allField['ID'] && $field['CODE'] == $namFld) {
                                                foreach ($usersFieldsSettings as $fieldSetting) {
                                                    if ($fieldSetting['FIELD_NAME'] == $namFld) {
                                                        if ($fieldSetting['MULTIPLE'] == 'Y') {
                                                            $serializeArray = explode($delimiter_r_f_char, $fld);
                                                            //foreach ()
                                                            $serializeArray = RemoveExcessItems($serializeArray);
//                                                            if (count($serializeArray[count($serializeArray) -1]) == "")
//                                                                unset($serializeArray[count($serializeArray) -1]);
                                                            FieldsTable::update($field['ID'], [
                                                                'VALUE'     => serialize($serializeArray),
                                                            ]);
                                                            break;
                                                        } else {
                                                            FieldsTable::update($field['ID'], [
                                                                'VALUE'     => $fld,
                                                            ]);
                                                            break;
                                                        }
                                                    }
                                                }
                                                break;

                                            }
                                        }

                                    }
                                }
                                $ck_fld = true;
                                break;
                            }
                        }
                    }

                    if ($ck_fld == false) {
                        RegionsTable::add($impField);
                        $line_num++;
                        foreach ($impField as $it => $field_to_imp) {
                            if (in_array($it, $usersFields)) {
                                foreach ($usersFieldsSettings as $fieldSetting) {
                                    if ($fieldSetting['FIELD_NAME'] == $it) {
                                        if ($fieldSetting['MULTIPLE'] == 'Y') {
                                            $serializeArray = explode($delimiter_r_f_char, $field_to_imp);

                                            $serializeArray = RemoveExcessItems($serializeArray);
//                                            if ($serializeArray[count($serializeArray)-1] == "") {
//                                                unset($serializeArray[count($serializeArray) -1]);
//                                            }
                                            FieldsTable::add([
                                                'ID_REGION' => $impField['ID'],
                                                'CODE' => $it,
                                                'VALUE' => serialize($serializeArray)
                                            ]);
                                        } else {
                                            FieldsTable::add([
                                                'ID_REGION' => $impField['ID'],
                                                'CODE' => $it,
                                                'VALUE' => $field_to_imp
                                            ]);
                                        }
                                    }
                                }
                            }
                        }

                    }

                }
            }

        }

        if (mb_strlen($strError) > 0)
        {
            $strError.= GetMessage(SotbitRegions::moduleId . "IBLOCK_ADM_IMP_TOTAL_ERRS")." ".$error_lines.".<br>";
            $strError.= GetMessage(SotbitRegions::moduleId . "IBLOCK_ADM_IMP_TOTAL_COR1")." ".$correct_lines." ".GetMessage(SotbitRegions::moduleId . "IBLOCK_ADM_IMP_TOTAL_COR2")."<br>";
            $STEP = 3;
        }
        //*****************************************************************//

    }
    //*****************************************************************//

}
/////////////////////////////////////////////////////////////////////
$APPLICATION->SetTitle(GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_PAGE_TITLE").$STEP);
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
CAdminMessage::ShowMessage($strError);
if (!$bAllLinesLoaded)
{
    $strParams = bitrix_sessid_get()."&CUR_FILE_POS=".$curFilePos."&CUR_LOAD_SESS_ID=".urlencode($CUR_LOAD_SESS_ID)."&STEP=4&URL_DATA_FILE=".urlencode($DATA_FILE_NAME)."&IBLOCK_ID=".$IBLOCK_ID."&fields_type=".urlencode($fields_type)."&max_execution_time=".IntVal($max_execution_time);
    if ($fields_type == "R")
        $strParams.= "&delimiter_r=".urlencode($delimiter_r)."&delimiter_other_r=".urlencode($delimiter_other_r)."&first_names_r=".urlencode($first_names_r);
    else
        $strParams.= "&metki_f=".urlencode($metki_f)."&first_names_f=".urlencode($first_names_f);
    ?>

    <?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_AUTO_REFRESH"); ?>
    <a href="<?echo $APPLICATION->GetCurPage(); ?>?lang=<?echo LANGUAGE_ID; ?>&<?echo $strParams ?>"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_AUTO_REFRESH_STEP"); ?></a><br>

    <script type="text/javascript">
        function DoNext()
        {
            window.location="<?echo $APPLICATION->GetCurPage(); ?>?lang=<?echo LANG ?>&<?echo $strParams ?>";
        }
        setTimeout('DoNext()', 2000);
    </script>
    <?
}
?>

    <form method="POST" action="<?=$APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID; ?>" ENCTYPE="multipart/form-data" name="dataload" id="dataload">
        <?$aTabs = array(
            array(
                "DIV" => "edit1",
                "TAB" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB1") ,
                "ICON" => "iblock",
                "TITLE" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB1_ALT"),
            ) ,
            array(
                "DIV" => "edit2",
                "TAB" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB2") ,
                "ICON" => "iblock",
                "TITLE" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB2_ALT"),
            ) ,
            array(
                "DIV" => "edit3",
                "TAB" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB3") ,
                "ICON" => "iblock",
                "TITLE" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB3_ALT"),
            ) ,
            array(
                "DIV" => "edit4",
                "TAB" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB4") ,
                "ICON" => "iblock",
                "TITLE" => GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB4_ALT"),
            ) ,
        );
        $tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
        $tabControl->Begin();
        $tabControl->BeginNextTab();

        if ($STEP == 1)
        {
            ?>
            <tr>
                <td width="40%"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_DATA_FILE"); ?></td>
                <td width="60%">
                    <input type="text" name="URL_DATA_FILE" value="<?echo htmlspecialcharsbx($URL_DATA_FILE); ?>" size="30">
                    <input type="button" value="<?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_OPEN"); ?>" OnClick="BtnClick()">
                    <?CAdminFileDialog::ShowScript(array(
                        "event" => "BtnClick",
                        "arResultDest" => array(
                            "FORM_NAME" => "dataload",
                            "FORM_ELEMENT_NAME" => "URL_DATA_FILE",
                        ) ,
                        "arPath" => array(
                            "SITE" => SITE_ID,
                            "PATH" => "/".COption::GetOptionString("main", "upload_dir", "upload"),
                        ) ,
                        "select" => 'F', // F - file only, D - folder only
                        "operation" => 'O', // O - open, S - save
                        "showUploadTab" => true,
                        "showAddToMenuTab" => false,
                        "fileFilter" => 'csv',
                        "allowAllFiles" => true,
                        "SaveConfig" => true,
                    ));
                    ?>
                </td>
            </tr>

            <tr>
                <td><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_INFOBLOCK"); ?></td>
                <td>
                    <?echo GetSiteDropDownListEx(
                        'CSITE_ID',
                        'IBLOCK_ID',
                        array(
                            "MIN_PERMISSION" => "X",
                            "OPERATION" => "iblock_import",
                        ),
                        '',
                        '',
                        'class="adm-detail-import-types"',
                        'class="adm-detail-import-list"'
                    );?>
                </td>
            </tr>
            <?
        }
        $tabControl->EndTab();
        $tabControl->BeginNextTab();
        if ($STEP == 2)
        {
            ?>
            <input type="hidden" name="CSITE_ID" value="<? echo $CSITE_ID; ?>">
            <tr id="table_r" class="heading">
                <td colspan="2"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_RAZDEL1"); ?></td>
            </tr>
            <tr id="table_r1">
                <td class="adm-detail-valign-top" style="width: 50%;"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_RAZDEL_TYPE"); ?> :</td>
                <td>
                    <input type="radio" name="delimiter_r" id="delimiter_r_TZP" value="TZP" <?
                    if ($delimiter_r == "TZP" || mb_strlen($delimiter_r) <= 0)
                        echo "checked" ?>><label for="delimiter_r_TZP"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TZP"); ?></label><br>
                    <input type="radio" name="delimiter_r" id="delimiter_r_ZPT" value="ZPT" <?
                    if ($delimiter_r == "ZPT")
                        echo "checked" ?>><label for="delimiter_r_ZPT"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_ZPT"); ?></label><br>
                    <input type="radio" name="delimiter_r" id="delimiter_r_TAB" value="TAB" <?
                    if ($delimiter_r == "TAB")
                        echo "checked" ?>><label for="delimiter_r_TAB"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_TAB"); ?></label><br>
                    <input type="radio" name="delimiter_r" id="delimiter_r_SPS" value="SPS" <?
                    if ($delimiter_r == "SPS")
                        echo "checked" ?>><label for="delimiter_r_SPS"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_SPS"); ?></label><br>
                    <input type="radio" name="delimiter_r" id="delimiter_r_OTR" value="OTR" <?
                    if ($delimiter_r == "OTR")
                        echo "checked" ?>><label for="delimiter_r_OTR"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_OTR"); ?></label>
                    <input type="text" name="delimiter_other_r" size="3" value="<?echo htmlspecialcharsbx($delimiter_other_r); ?>">
                </td>
            </tr>
            <tr id="table_r2">
                <td><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_FIRST_NAMES"); ?>:</td>
                <td>
                    <input type="hidden" name="first_names_r" id="first_names_r_N" value="N">
                    <input type="checkbox" name="first_names_r" id="first_names_r_Y" value="Y" <?
                    if ($first_names_r != "N")
                        echo "checked" ?>>
                </td>
            </tr>

            <tr class="heading">
                <td colspan="2">
                    <?echo GetMessage(SotbitRegions::moduleId .  "_EXPORT_ADM_EXP_IMP_DELIMITER") ?>
                    <input type="hidden" name="fields_type" value="R">
                </td>
            </tr>
            <tr>
                <td width="40%" class="adm-detail-valign-top"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_IMP_DELIMITER") ?>:</td>
                <td width="60%">
                    <input type="radio" name="delimiter_r_f" id="delimiter_TZP" value="TZP" <?if ($delimiter_r_f=="TZP" || mb_strlen($delimiter_r_f)<=0) echo "checked"?>><label for="delimiter_TZP"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_IMP_DELIM_TZP") ?></label><br>
                    <input type="radio" name="delimiter_r_f" id="delimiter_ZPT" value="ZPT" <?if ($delimiter_r_f=="ZPT") echo "checked"?>><label for="delimiter_ZPT"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_IMP_DELIM_ZPT") ?></label><br>
                    <input type="radio" name="delimiter_r_f" id="delimiter_TAB" value="TAB" <?if ($delimiter_r_f=="TAB") echo "checked"?>><label for="delimiter_TAB"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_IMP_DELIM_TAB") ?></label><br>
                    <input type="radio" name="delimiter_r_f" id="delimiter_SPS" value="SPS" <?if ($delimiter_r_f=="SPS") echo "checked"?>><label for="delimiter_SPS"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_IMP_DELIM_SPS") ?></label><br>
                    <input type="radio" name="delimiter_r_f" id="delimiter_OTR" value="OTR" <?if ($delimiter_r_f=="OTR") echo "checked"?>><label for="delimiter_OTR"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_EXP_IMP_DELIM_OTR") ?></label>
                    <input type="text" name="delimiter_r_f_char" size="3" value="<?echo htmlspecialcharsbx($delimiter_r_f) ?>">
                </td>
            </tr>

            <tr class="heading">
                <td colspan="2"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_DATA_SAMPLES"); ?></td>
            </tr>
            <tr>
                <td align="center" colspan="2">
                    <?$sContent = "";
                    if (mb_strlen($DATA_FILE_NAME) > 0)
                    {
                        $DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)) , "/");
                        $FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
                        if (
                            (mb_strlen($FILE_NAME) > 1)
                            && ($FILE_NAME == "/".$DATA_FILE_NAME)
                            && $APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W"
                        )
                        {
                            $f = $io->GetFile($_SERVER["DOCUMENT_ROOT"].$FILE_NAME);
                            $file_id = $f->open("r");
                            $sContent = '';
                            $lContent = 0;
                            while (($line = fgets($file_id)) !== false)
                            {
                                if (ToLower(SITE_CHARSET) == 'utf-8') {
                                    $sContent .= iconv('cp1251', 'utf-8', $line);
                                } else {
                                    $sContent .= $line;
                                }

                                $lContent += mb_strlen($line);
                                if ($lContent > 10000)
                                    break;
                            }
                            fclose($file_id);
                        }
                    }
                    ?>
                    <textarea name="data" rows="10" cols="80" style="width:100%"><?echo htmlspecialcharsbx($sContent); ?></textarea>
                </td>
            </tr>
            <?
        }
        $tabControl->EndTab();

        $tabControl->BeginNextTab();
        if ($STEP == 3)
        {
            ?>
            <input type="hidden" name="CSITE_ID" value="<? echo $CSITE_ID; ?>">
            <input type="hidden" name="delimiter_r_f" value="<? echo $delimiter_r_f; ?>">
            <input type="hidden" name="delimiter_r_f_char" value="<? echo $delimiter_r_f_char; ?>">
            <tr class="heading">
                <td colspan="2"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_FIELDS_SOOT"); ?></td>
            </tr>

            <?
            $arRegions = RegionsTable::getList()->fetchAll();
            $arFields = FieldsTable::getList()->fetchAll();

            $arDataFileFieldsArray = array();

            foreach ($arRegions as $key => $region) {
                $arDataFileFieldsArray[$key] = $region;
                foreach ($arFields as $field) {
                    if ($field['ID_REGION'] == $region['ID']) {
                        $arDataFileFieldsArray[$key] += [$field["CODE"] => $field["VALUE"]];
                    }
                }
            }

            unset($arDataFileFieldsArray[0]['SITE_ID']);
            $dbRes = CUserTypeEntity::GetList(
                array(),
                array('ENTITY_ID' => 'SOTBIT_REGIONS' , 'FIELD_NAME' => $keys, 'LANG' => $lang)
            );

            $userEntity = array();
            while ($item = $dbRes->Fetch()) {
                $userEntity[] = $item;
            }

            $rdyArray = array();
            foreach ($userEntity as $key => $item) {
                $rdyArray[] = array(
                    'FIELD_NAME' => $item['FIELD_NAME'],
                    'TITLE' => $item['EDIT_FORM_LABEL']
                );
            }

            /* Regions */
            $regionEntity = RegionsTable::getEntity()->getFields();
            $rdyArrayRegion[] = array();
            foreach ($regionEntity as $key => $value) {
                $rdyArrayRegion[] = array(
                    'FIELD_NAME' => $key,
                    'TITLE' => $value->getTitle()
                );
            }

            $namedArrayFields = array_merge($rdyArrayRegion, $rdyArray);
            
            unset($namedArrayFields[0]);
            foreach ($namedArrayFields as $key => $value) {
                if ($value['FIELD_NAME'] == 'SITE_ID') {
                    unset($namedArrayFields[$key]);
                }
            }

            foreach ($arDataFileFields as $key => $it) {
                if ($it == "") {
                    unset($arDataFileFields[$key]);
                }
            }

            foreach ($arDataFileFields as $i => $field)
            {
                ?>
                <tr>
                    <td width="40%">
                        <b><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_FIELD"); ?> <?echo $i + 1 ?></b> (<?echo htmlspecialcharsbx($field); ?>):
                    </td>
                    <td width=60"%">
                        <select name="field_<?echo $i?>">
                            <option value=""> - </option>
                            <? foreach ($namedArrayFields as $key => $value) { ?>
                                <option value="<?echo htmlspecialcharsbx($value['FIELD_NAME']); ?>"><? echo $value['FIELD_NAME'] . ' <i>(' . $value['TITLE'] . ')</i>'; ?></option>
                            <? } ?>
                        </select>
                    </td>
                </tr>
                <?
            }
            ?>

            <tr class="heading">
                <td colspan="2"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_ADDIT_SETTINGS"); ?></td>
            </tr>
            <tr>
                <td width="40%">
                    <b><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_UPDATE_REGIONS"); ?></b>:
                </td>
                <td width="60%">
                    <select name="field_update">

                        <option value=""> - </option>
                        <? foreach ($namedArrayFields as $key => $value) {
                            if ($value['FIELD_NAME'] != 'ID') {
                                ?>
                                <option value="<?echo htmlspecialcharsbx($value['FIELD_NAME']); ?>"><? echo $value['FIELD_NAME'] . ' (' . $value['TITLE'] . ')'; ?></option>
                            <? }} ?>

                    </select>
                    <br>
                    <label for="field_update"><small><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_UPDATE_REGIONS_ABOUT"); ?></small></label>
                </td>
            </tr>


            <tr class="heading">
                <td colspan="2"><?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_DATA_SAMPLES"); ?></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <?$sContent = "";
                    if (mb_strlen($DATA_FILE_NAME) > 0)
                    {
                        $DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)) , "/");
                        $FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
                        if (
                            (mb_strlen($FILE_NAME) > 1)
                            && ($FILE_NAME == "/".$DATA_FILE_NAME)
                            && $APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W"
                        )
                        {
                            $f = $io->GetFile($_SERVER["DOCUMENT_ROOT"].$FILE_NAME);
                            $file_id = $f->open("r");
                            $sContent = '';
                            $lContent = 0;
                            while (($line = fgets($file_id)) !== false)
                            {
                                if (ToLower(SITE_CHARSET) == 'utf-8') {
                                    $sContent .= iconv('cp1251', 'utf-8', $line);
                                } else {
                                    $sContent .= $line;
                                }
                                $lContent += mb_strlen($line);
                                if ($lContent > 10000)
                                    break;
                            }
                            fclose($file_id);
                        }
                    }
                    ?>
                    <textarea name="data" rows="10" cols="80" style="width:100%"><?echo htmlspecialcharsbx($sContent); ?></textarea>
                </td>
            </tr>
            <?
        }
        $tabControl->EndTab();
        $tabControl->BeginNextTab();
        if ($STEP == 4)
        {
            ?>
            <input type="hidden" name="CSITE_ID" value="<? echo $CSITE_ID; ?>">
            <tr>
                <td>
                    <? CAdminMessage::ShowMessage(array(
                        "TYPE" => "PROGRESS",
                        "MESSAGE" => !$bAllLinesLoaded? GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_AUTO_REFRESH_CONTINUE"): GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_SUCCESS"),
                        "DETAILS" =>
                            GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_SU_ALL").' <b>'. (string)($line_num + $correct_lines) .'</b><br>'
                            .GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_SU_CORR").' <b>'.$line_num.'</b><br>'
                            .GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_SU_ER").' <b>'.$correct_lines.'</b><br>'
                    ,
                        "HTML" => true,
                    ))?>
                </td>
            </tr>
            <?
        }
        $tabControl->EndTab();

        $tabControl->Buttons();

        if ($STEP < 4): ?>
        <input type="hidden" name="STEP" value="<?echo $STEP + 1; ?>">
        <?echo bitrix_sessid_post(); ?>
        <?
        if ($STEP > 1): ?>
        <input type="hidden" name="URL_DATA_FILE" value="<?echo htmlspecialcharsbx($DATA_FILE_NAME); ?>">
        <input type="hidden" name="IBLOCK_ID" value="<?echo $IBLOCK_ID ?>">
        <?
        endif; ?>

        <?
        if ($STEP <> 2): ?>
        <input type="hidden" name="fields_type" value="<?echo htmlspecialcharsbx($fields_type); ?>">
        <input type="hidden" name="delimiter_r" value="<?echo htmlspecialcharsbx($delimiter_r); ?>">
        <input type="hidden" name="delimiter_other_r" value="<?echo htmlspecialcharsbx($delimiter_other_r); ?>">
        <input type="hidden" name="first_names_r" value="<?echo htmlspecialcharsbx($first_names_r); ?>">
        <input type="hidden" name="metki_f" value="<?echo htmlspecialcharsbx($metki_f); ?>">
        <input type="hidden" name="first_names_f" value="<?echo htmlspecialcharsbx($first_names_f); ?>">
        <?
        endif; ?>

        <?
        if ($STEP <> 3): ?>
        <?
        foreach ($_POST as $name => $value): ?>
        <?
        if (preg_match("/^field_(\\d+)$/", $name)): ?>
        <input type="hidden" name="<?echo $name ?>" value="<?echo htmlspecialcharsbx($value); ?>">
        <?
        endif
            ?>
        <?
        endforeach ?>
        <input type="hidden" name="PATH2IMAGE_FILES" value="<?echo htmlspecialcharsbx($PATH2IMAGE_FILES); ?>">
        <input type="hidden" name="IMAGE_RESIZE" value="<?echo htmlspecialcharsbx($IMAGE_RESIZE); ?>">
        <input type="hidden" name="PATH2PROP_FILES" value="<?echo htmlspecialcharsbx($PATH2PROP_FILES); ?>">
        <input type="hidden" name="outFileAction" value="<?echo htmlspecialcharsbx($outFileAction); ?>">
        <input type="hidden" name="inFileAction" value="<?echo htmlspecialcharsbx($inFileAction); ?>">
        <input type="hidden" name="max_execution_time" value="<?echo htmlspecialcharsbx($max_execution_time); ?>">
        <?
        endif; ?>

        <?
        if ($STEP > 1): ?>
        <input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_BACK"); ?>">
        <?
        endif
        ?>
        <input type="submit" <?echo ($STEP == 3) ? "disabled" : "" ?> value="<?echo ($STEP == 3) ? GetMessage
        (SotbitRegions::moduleId
            . "_EXPORT_ADM_IMP_NEXT_STEP_F") : GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_NEXT_STEP"); ?> &gt;&gt;" name="submit_btn" class="adm-btn-save">

        <?if ($STEP == 2)
        {
        ?>
            <script type="text/javascript">
                DeactivateAllExtra();
                ChangeExtra();
            </script>
        <?
        }
        ?>
        <?
        else: ?>
        <input type="submit" name="backButton2" value="&lt;&lt; <?echo GetMessage(SotbitRegions::moduleId . "_EXPORT_ADM_IMP_2_1_STEP"); ?>" class="adm-btn-save">
        <?
        endif;

        $tabControl->End();
        ?>
    </form>
    <script type="text/javascript">
        <?if ($STEP < 2): ?>
        tabControl.SelectTab("edit1");
        tabControl.DisableTab("edit2");
        tabControl.DisableTab("edit3");
        tabControl.DisableTab("edit4");
        <?elseif ($STEP == 2): ?>
        tabControl.SelectTab("edit2");
        tabControl.DisableTab("edit1");
        tabControl.DisableTab("edit3");
        tabControl.DisableTab("edit4");
        <?elseif ($STEP == 3): ?>
        tabControl.SelectTab("edit3");
        tabControl.DisableTab("edit1");
        tabControl.DisableTab("edit2");
        tabControl.DisableTab("edit4");

        // Disable next button is empty compare select
        document.querySelector('select[name="field_update"]').addEventListener('change', function (e) {
            if(e.target.value !== "")
                document.querySelector('input[name="submit_btn"]').removeAttribute('disabled');
            else
                document.querySelector('input[name="submit_btn"]').setAttribute('disabled', 'disabled');
        });

        <?elseif ($STEP > 3): ?>
        tabControl.SelectTab("edit4");
        tabControl.DisableTab("edit1");
        tabControl.DisableTab("edit2");
        tabControl.DisableTab("edit3");
        <?endif; ?>
    </script>
<?
require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");

?>