<?php
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Internals\FieldsTable;
use Sotbit\Regions\Internals\RegionsTable;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!Loader::includeModule('sotbit.regions'))
{
	return false;
}


global $APPLICATION, $USER, $USER_FIELD_MANAGER;

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage(\SotbitRegions::moduleId.'_TITLE'));

if(\SotbitRegions::isDemoEnd())
{
	return false;
}

$sites = [];
$rsSites = \Bitrix\Main\SiteTable::getList(array(
	'select' => array(
		'SITE_NAME',
		'LID'
	),
	'filter' => array('ACTIVE' => 'Y', 'LID' => $_REQUEST['site']),
));
while ($site = $rsSites->fetch())
{
	$sites[$site['LID']] = '['.$site['LID'].'] '.$site['SITE_NAME'];
}

$sTableID = 'sotbit_regions';
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders = array(
    array(
        'id' => 'ID',
        'content' => 'ID',
        'sort' => 'ID',
    ),
    array(
		'id' => 'CODE',
		'content' => Loc::getMessage(\SotbitRegions::moduleId.'_CODE'),
		'sort' => 'CODE',
		'default' => true
	),
	array(
		'id' => 'NAME',
		'content' => Loc::getMessage(\SotbitRegions::moduleId.'_NAME'),
		'sort' => 'NAME',
	),

	array(
		'id' => 'SORT',
		'content' => Loc::getMessage(\SotbitRegions::moduleId.'_SORT'),
		'sort' => 'SORT',
	),
/*����������� �������� ����� � �������*/
//	array(
//		'id' => 'SITE_ID',
//		'content' => Loc::getMessage(\SotbitRegions::moduleId.'_SITE'),
//		'sort' => 'SITE_ID',
//	),
);

if(Loader::includeModule('catalog'))
{
	$arHeaders[] = 	array(
		'id' => 'PRICE_CODE',
		'content' => Loc::getMessage(\SotbitRegions::moduleId.'_PRICE_CODE'),
		'sort' => 'PRICE_CODE',
	);	
	$arHeaders[] = 	array(
		'id' => 'STORE',
		'content' => Loc::getMessage(\SotbitRegions::moduleId.'_STORE'),
		'sort' => 'STORE',
	);
	
}

$ufEntityId = 'SOTBIT_REGIONS';
$USER_FIELD_MANAGER->AdminListAddHeaders($ufEntityId, $arHeaders);

// show all columns by default
foreach ($arHeaders as &$arHeader)
{
	$arHeader['default'] = true;
}
unset($arHeader);

$lAdmin->AddHeaders($arHeaders);

if (!in_array($by, $lAdmin->GetVisibleHeaderColumns(), true))
{
	$by = 'CODE';
}

// add filter
$filter = null;

$filterFields = array('find_i', 'find_c','find_n','find_s','find_site', 'find_pr', 'find_sc');
$filterValues = array();
$filterTitles = array('ID', 'CODE','NAME','SORT','SITE_ID', 'PRICE_CODE', 'STORE');

$USER_FIELD_MANAGER->AdminListAddFilterFields($ufEntityId, $filterFields);

$filter = $lAdmin->InitFilter($filterFields);

if (!empty($find_i))
{
    $filterValues['ID'] = $find_i;
}
if (!empty($find_c))
{
	$filterValues['CODE'] = $find_c;
}
if (!empty($find_n))
{
	$filterValues['NAME'] = $find_n;
}
if (!empty($find_s))
{
	$filterValues['SORT'] = $find_s;
}
if (!empty($find_site))
{
	$filterValues['SITE_ID'] = $find_site;
}

if (!empty($find_pr))
{
    $filterValues['PRICE_CODE'] = $find_pr;
}

if (!empty($find_sc))
{
    $filterValues['STORE'] = $find_sc;
}

$USER_FIELD_MANAGER->AdminListAddFilter($ufEntityId, $filterValues);
$USER_FIELD_MANAGER->AddFindFields($ufEntityId, $filterTitles);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	$filterTitles
);

// group actions
if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			continue;

		if(!$lAdmin->IsUpdated($ID))
			continue;


        $rs = RegionsTable::update($ID, [
            'ID'         => $arFields['ID'],
            'CODE'       => $arFields['CODE'],
            'NAME'       => $arFields['NAME'],
            'SORT'       => $arFields['SORT'],
            //'SITE_ID'    => $arFields['SITE_ID'],
            'PRICE_CODE' => $arFields['PRICE_CODE'],
            'STORE'      => $arFields['STORE'],
        ]);
        unset($arFields['ID'], $arFields['CODE'], $arFields['NAME'], $arFields['SORT'], /*$arFields['SITE_ID'],*/
            $arFields['PRICE_CODE'], $arFields['STORE']);

        if(!$rs->isSuccess())
		{
			$lAdmin->AddGroupError(implode(', ',$rs->getErrorMessages()));
		}

        // custom fields update
		$rs = FieldsTable::getList(array('filter' => array('ID_REGION' => $ID)));
		while ($field = $rs->fetch())
		{
			if($arFields[$field['CODE']])
			{
				if(isset($arFields[$field['CODE']]) && is_array($arFields[$field['CODE']]))
				{
                    // remove empty values and serialize
					$arFields[$field['CODE']] = serialize(array_diff($arFields[$field['CODE']], array('')));
				}
				FieldsTable::update($field['ID'],array('VALUE' => $arFields[$field['CODE']]));
				unset($arFields[$field['CODE']]);
			}
		}

		if($arFields)
		{
			foreach($arFields as $code => $value)
			{
				if(is_array($value))
				{
					$value = serialize($value);
				}
				FieldsTable::add(array('ID_REGION' => $ID,'CODE' => $code,'VALUE' => $value));
			}
		}
	}
}

if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = array();

		$rsData = $entity_data_class::getList(array(
			"select" => array('ID'),
			"filter" => $filterValues
		));

		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach ($arID as $ID)
	{
		$ID = (int)$ID;

		if (!$ID)
		{
			continue;
		}

		switch($_REQUEST['action'])
		{
			case "delete":
				$rs = FieldsTable::getList(array('filter' => array('ID_REGION' => $ID)));
				while($field = $rs->fetch())
				{
					FieldsTable::delete($field['ID']);
				}
				RegionsTable::delete($ID);
				break;
		}
	}
}
$lAdmin->AddGroupActionTable(Array(
	"delete"    => Loc::getMessage(SotbitRegions::moduleId.'_DELETE'),
));


// select data
/** @var string $order */
$order = strtoupper($order);

$usePageNavigation = true;
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
{
	$usePageNavigation = false;
}
else
{
	$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize(
		$sTableID,
		array('nPageSize' => 20, 'sNavID' => $APPLICATION->GetCurPage().'?ENTITY_ID='.$ENTITY_ID)
	));
	if ($navyParams['SHOW_ALL'])
	{
		$usePageNavigation = false;
	}
	else
	{
		$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
		$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
	}
}
$selectFields = $lAdmin->GetVisibleHeaderColumns();

foreach($selectFields as $k => $field)
{
	if(!in_array($field,array('CODE','NAME','SORT','SITE_ID','PRICE_CODE','STORE')))
	{
		unset($selectFields[$k]);
	}
}

$sort = $by;

if(!in_array($by,array('CODE','NAME','SORT','SITE_ID','PRICE_CODE','STORE')))
{
	$by = 'ID';
}


$filterValues['%SITE_ID'] = $_REQUEST['site'];
//unset($filterValues['SITE_ID']);

if (!in_array('ID', $selectFields))
	$selectFields[] = 'ID';
$getListParams = array(
	'select' => $selectFields,
	'filter' => $filterValues,
	'order' => array($by => $order)
);
unset($filterValues, $selectFields);
if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

if ($usePageNavigation)
{
	$countQuery = new Query(RegionsTable::getEntity());
	$countQuery->addSelect(new ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($getListParams['filter']);
	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];
	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);
		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	}
	else
	{
		$navyParams['PAGEN'] = 1;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = 0;
	}
}
$ids = array();
array_push($getListParams['filter'], ['%SITE_ID' => $_REQUEST['site']]);
$rsData = new CAdminResult(RegionsTable::getList($getListParams), $sTableID);
if ($usePageNavigation)
{
	$rsData->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$rsData->NavRecordCount = $totalCount;
	$rsData->NavPageCount = $totalPages;
	$rsData->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$rsData->NavStart();
}
// build list
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage(\SotbitRegions::moduleId.'_PAGES')));

$data = array();


while($arRes = $rsData->NavNext(true, "f_"))
{
	$data[$arRes['ID']] = $arRes;
}

$rs = FieldsTable::getList(array('filter' => array('ID_REGION' => array_keys($data))));
while($field = $rs->fetch())
{
	$data[$field['ID_REGION']][$field['CODE']] = $field['VALUE'];
}

if(!in_array($sort,array('ID', 'CODE','NAME','SORT','SITE_ID','PRICE_CODE','STORE')))
{
	$sorting = [];
	foreach($data as $idRegion => $region)
	{
		$sorting[$idRegion] = $region[$sort];
	}
	if($order == 'ASC')
	{
		asort($sorting);
	}
	else
	{
		arsort($sorting);
	}
	$tmp = $data;
	$data = [];
	foreach($sorting as $id => $v)
	{
		$data[$id] = $tmp[$id];
	}
}

$priceCodes = array();
$stores = array();
if(Loader::includeModule('catalog'))
{
	$rs = CCatalogGroup::GetList(array(), array('ACTIVE' => 'Y'));
	while ($priceCode = $rs->fetch())
	{
		$priceCodes[$priceCode['NAME']] = $priceCode['NAME_LANG'];
	}

	$rs = \CCatalogStore::GetList(
		array(),
		array(
			'ISSUING_CENTER' => 'Y',
			'ACTIVE' => 'Y'
		),
		false,
		false,
		array(
			'ID',
			'TITLE'
		)
	);
	while ($store = $rs->fetch())
	{
		$stores[$store['ID']] = $store['TITLE'];
	}
}

foreach($data as $id => $arRes)
{
	if(!is_array($arRes['SITE_ID']))
	{
		$arRes['SITE_ID'] = [];
	}
	$optionsSite = '';
	foreach ($sites as $lid => $site)
	{
		$optionsSite .= '<option value="' . $lid . '" ' . ((in_array($lid, $arRes['SITE_ID'])) ? 'selected' : '')
			. '>' . $site . '</option>';
	}

	$optionsPrice = '';
	foreach ($priceCodes as $code => $name)
	{
		$optionsPrice .= '<option value="' . $code . '" ' . ((in_array($code, $arRes['PRICE_CODE'])) ? 'selected' : '')
			. '>' . $name . '</option>';
	}
	$optionsStore = '';
	foreach ($stores as $code => $name)
	{
		$optionsStore .= '<option value="' . $code . '" ' . ((is_array($arRes['STORE']) && in_array($code, $arRes['STORE'])) ? 'selected' : '')
			. '>' . $name . '</option>';
	}

	$arRes['SITE_ID'] = implode(' / ', $arRes['SITE_ID']);

    if(isset($arRes['PRICE_CODE'])) {
        foreach ($arRes['PRICE_CODE'] as $i => $code) {
            $arRes['PRICE_CODE'][$i] = $priceCodes[$code];
        }
        $arRes['PRICE_CODE'] = implode(' / ', $arRes['PRICE_CODE']);
    }
    if(isset($arRes['STORE'])) {
        foreach ($arRes['STORE'] as $i => $code) {
            $arRes['STORE'][$i] = $stores[$code];
        }
        $arRes['STORE'] = implode(' / ', $arRes['STORE']);
    }

	$row = $lAdmin->AddRow($arRes['ID'], $arRes);
	$row->AddEditField('ID', '<a href="' . 'sotbit_regions_edit.php?ID='.$arRes['ID'].'&lang='
		.LANGUAGE_ID .	'">'.$arRes['ID'].'</a>');
	$row->AddInputField("CODE");
	$row->AddInputField("NAME");
	$row->AddInputField("SORT");
	$row->AddEditField("SITE_ID",'<select multiple name="FIELDS['.$id.'][SITE_ID][]">'.$optionsSite.'</select>');
	if(Loader::includeModule('catalog'))
	{
		$row->AddEditField("PRICE_CODE",'<select multiple name="FIELDS['.$id.'][PRICE_CODE][]">'.$optionsPrice.'</select>');
		$row->AddEditField("STORE",'<select multiple name="FIELDS['.$id.'][STORE][]">'.$optionsStore.'</select>');
	}

	$USER_FIELD_MANAGER->AddUserFields('SOTBIT_REGIONS', $arRes, $row);

	$arActions = array();

	$arActions[] = array(
		'ICON' => 'edit',
		'TEXT' => Loc::getMessage(\SotbitRegions::moduleId.'_EDIT'),
		'ACTION' => $lAdmin->ActionRedirect('sotbit_regions_edit.php?ID='.$arRes['ID'].'&lang='
			.LANGUAGE_ID.'&site='.$_REQUEST['site']),
		'DEFAULT' => true
	);

	$arActions[] = array(
		'ICON'=>'delete',
		'TEXT' => Loc::getMessage(\SotbitRegions::moduleId.'_DELETE'),
		'ACTION' => 'if(confirm(\''.Loc::getMessage(\SotbitRegions::moduleId.'_DELETE_CONFIRM').'\')) '.
			$lAdmin->ActionRedirect('sotbit_regions_edit.php?action=delete&ID='.$arRes['ID'].'&lang='.LANGUAGE_ID.'&site='.$_REQUEST['site'].'&'
				.bitrix_sessid_get())
	);

	$row->AddActions($arActions);
}


// view
$menu = array();

$menu[] = array(
	'TEXT'	=> Loc::getMessage(\SotbitRegions::moduleId.'_ADD'),
	'TITLE'	=> Loc::getMessage(\SotbitRegions::moduleId.'_ADD'),
	'LINK'	=> 'sotbit_regions_edit.php?lang='.LANGUAGE_ID.'&site='.$_REQUEST['site'],
	'ICON'	=> 'btn_new'
);

$lAdmin->AddAdminContextMenu($menu);

$lAdmin->CheckListMode();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		Loc::getMessage(\SotbitRegions::moduleId.'_CODE'),
		Loc::getMessage(\SotbitRegions::moduleId.'_NAME'),
		Loc::getMessage(\SotbitRegions::moduleId.'_SORT'),
        Loc::getMessage(\SotbitRegions::moduleId.'_SITE'),
        Loc::getMessage(\SotbitRegions::moduleId.'_PRICE_CODE'),
        Loc::getMessage(\SotbitRegions::moduleId.'_STORE'),
	)
);
?>
	<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurUri();?>">
		<?
		$oFilter->Begin();
		?>
		<tr>
			<td><?=Loc::getMessage(\SotbitRegions::moduleId.'_CODE')?>:</td>
			<td>
				<input type="text" name="find_c" size="47" value="<?echo htmlspecialchars($find_c)?>">
			</td>
		</tr>
		<tr>
			<td><?=Loc::getMessage(\SotbitRegions::moduleId.'_NAME')?>:</td>
			<td>
				<input type="text" name="find_n" size="47" value="<?echo htmlspecialchars($find_n)?>">
			</td>
		</tr>
		<tr>
			<td><?=Loc::getMessage(\SotbitRegions::moduleId.'_SORT')?>:</td>
			<td>
				<input type="text" name="find_s" size="47" value="<?echo htmlspecialchars($find_s)?>">
			</td>
		</tr>
		<tr>
			<td><?=Loc::getMessage(\SotbitRegions::moduleId.'_SITE')?>:</td>
			<td>
				<select name="find_site">
					<option value=""><?=Loc::getMessage(\SotbitRegions::moduleId.'_SITE_ANY')?></option>
					<?foreach($sites as $lid => $site)
					{
						?>
						<option <?=($lid == $find_site)?'selected':''?> value="<?=$lid?>"><?=$site?></option>
						<?
					}
					?>
				</select>
			</td>
		</tr>
        <tr>
            <td><?=Loc::getMessage(\SotbitRegions::moduleId.'_PRICE_CODE')?>:</td>
            <td>
                <select name="find_pr">
                    <option value=""><?=Loc::getMessage(\SotbitRegions::moduleId.'_SITE_ANY')?></option>
                    <?foreach($priceCodes as $lid => $code)
                    {
                        ?>
                        <option <?=($lid == $find_pr)?'selected':''?> value='<?= serialize(array(0 => $lid)) ?>'><?=$code?></option>
                        <?
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?=Loc::getMessage(\SotbitRegions::moduleId.'_STORE')?>:</td>
            <td>
                <select name="find_sc">
                    <option value=""><?=Loc::getMessage(\SotbitRegions::moduleId.'_SITE_ANY')?></option>
                    <?foreach($stores as $lid => $code)
                    {
                        ?>
                        <option <?=($lid == $find_sc)?'selected':''?> value='<?= serialize(array(0 => (string)$lid)) ?>'><?=$code?></option>
                        <?
                    }
                    ?>
                </select>
            </td>
        </tr>
		<?
		$USER_FIELD_MANAGER->AdminListShowFilter($ufEntityId);
		$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurUri(),"form"=>"find_form"));
		$oFilter->End();
		?>
	</form>
<?

if(\SotbitRegions::getDemo() == 2)
{
	CAdminMessage::ShowMessage(array("MESSAGE"=>Loc::getMessage(\SotbitRegions::moduleId."_DEMO"),
		'HTML'=>true));
}
if(\SotbitRegions::getDemo() == 3)
{
	CAdminMessage::ShowMessage(array("MESSAGE"=>Loc::getMessage(\SotbitRegions::moduleId."_DEMO_END"),
		'HTML'=>true));
}


$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>