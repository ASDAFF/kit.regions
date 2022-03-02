<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Internals\FieldsTable;
use Sotbit\Regions\Internals\RegionsTable;
use Sotbit\Regions\Internals\LocationsTable;
use Bitrix\Sale\Internals\Input;
use Bitrix\Main\Web;
use Bitrix\Main\Context;
use Bitrix\Main\SiteDomainTable;

define('ENTITY_ID', 'SOTBIT_REGIONS');
define('MODULE_ID', 'sotbit.regions');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

CUtil::InitJSCore(['jquery']);
Loc::loadMessages(__FILE__);

$request = $siteId = Context::getCurrent()->getRequest();
$siteId = $request->getQuery('site');
$sitedDomen = SiteDomainTable::query()
    ->addSelect('DOMAIN')
    ->where('LID', $siteId)
    ->fetch()
;
$prtorocol = $request->isHttps() ? 'https://' : 'http://';
$sitedDomen = $sitedDomen ? $prtorocol.$sitedDomen['DOMAIN'] : false;

?>

    <style>
        .adm-detail-content-cell-l img {
            display: none;
        }
    </style>
    <script>
        window.addEventListener('load', function() {
            const defDomFild = document.getElementById('DEFAULT_DOMAIN');
            const defDom = defDomFild.querySelector('label');
            const domenFild = document.getElementById('tr_CODE');
            const domen = domenFild.querySelector('input');
            defDom.addEventListener('click', function(e) {
                const checkBox = defDomFild.querySelector('input');
                if(checkBox.checked) {
                    return;
                }
                const rootDomen = '<?=$sitedDomen?>';
                const cuurentDomen = domen.value;
                if (rootDomen && rootDomen !== cuurentDomen) {
                    e.preventDefault();
                    return alert('<?=Loc::getMessage('sotbit_regions._DOFOLT_DOMEN_SHOULD_BE')?>'+rootDomen)
                }
            })
        })
    </script>

<?php

if (!Loader::includeModule(MODULE_ID)
    || !Loader::includeModule('fileman')
) {
    return false;
}

if (\SotbitRegions::isDemoEnd()) {
    return false;
}

$action = isset($_REQUEST['action']) ? htmlspecialcharsbx($_REQUEST['action'])
    : 'add';

// get row
$row = null;
$arrKeyClean = [];
$issetDefaultDomain = false;

$allRegions = RegionsTable::GetList([
    'order' => ["ID" => "asc"],
])->fetchAll();

if (isset($_REQUEST['ID']) && $_REQUEST['ID'] > 0 && isset($allRegions) && is_array($allRegions)) {
    $site_request = ToLower($_REQUEST['site']);


    foreach ($allRegions as $key => $region) {
        if($_REQUEST['DEFAULT_DOMAIN'] == 'Y' && $region['DEFAULT_DOMAIN'] == 'Y' && $_REQUEST['ID'] !=  $region['ID'] && ToLower($region['SITE_ID'][0]) == $site_request)
        if ($_REQUEST['DEFAULT_DOMAIN'] == 'Y' && $region['DEFAULT_DOMAIN'] == 'Y'
            && $_REQUEST['ID'] != $region['ID']
        ) {
            $arrKeyClean[] = $key;
        }
        if ($region['ID'] == $_REQUEST['ID']) {
            $row = $region;
        }
        if($region['DEFAULT_DOMAIN'] == 'Y' && $region['ID'] != $_REQUEST['ID']) {
            $issetDefaultDomain = true;
        }
    }

    if ($_REQUEST['DEFAULT_DOMAIN'] == 'Y' && !empty($arrKeyClean)) {
        foreach ($arrKeyClean as $item) {
            if ($allRegions[$item]['DEFAULT_DOMAIN'] == 'Y') {
                $allRegions[$item]['DEFAULT_DOMAIN'] = 'N';
                $result = RegionsTable::update($allRegions[$item]['ID'], $allRegions[$item]);
            }
        }
    }

//    $row = RegionsTable::getById($_REQUEST['ID'])->fetch();

    if ($row['ID'] > 0) {
        $rs = FieldsTable::getList(['filter' => ['ID_REGION' => $row['ID']]]);
        while ($field = $rs->fetch()) {
            $row[$field['CODE']] = $field['VALUE'];
        }
    }
    $row['LOCATION_ID'] = [];
    if ($row['ID'] > 0) {
        $rs = LocationsTable::getList(['filter' => ['REGION_ID' => $row['ID']]]);
        while ($field = $rs->fetch()) {
            $row['LOCATION_ID'][] = $field['LOCATION_ID'];
        }
    }

    if (empty($row)) {
        $row = null;
    }
}
if (empty($row)) {
    if (isset($_REQUEST["CODE"]) && $_REQUEST["CODE"]) {
        $row["CODE"] = $_REQUEST["CODE"];
    }
    if (isset($_REQUEST["DEFAULT_DOMAIN"]) && $_REQUEST["DEFAULT_DOMAIN"]) {
        $row["DEFAULT_DOMAIN"] = $_REQUEST["DEFAULT_DOMAIN"];
    } else {
        if (!isset($_REQUEST["DEFAULT_DOMAIN"]) && !$_REQUEST["DEFAULT_DOMAIN"] && !empty($allRegions)) {
            $row["DEFAULT_DOMAIN"] = "N";
        } else {
            $row["DEFAULT_DOMAIN"] = "Y";
        }
    }

    if (isset($_REQUEST["NAME"]) && $_REQUEST["NAME"]) {
        $row["NAME"] = $_REQUEST["NAME"];
    }
    if (isset($_REQUEST["SORT"]) && $_REQUEST["SORT"]) {
        $row["SORT"] = $_REQUEST["SORT"];
    }
    if (isset($_REQUEST["PRICE_CODE"]) && $_REQUEST["PRICE_CODE"]) {
        $row["PRICE_CODE"] = $_REQUEST["PRICE_CODE"];
    }
    if (isset($_REQUEST["STORE"]) && $_REQUEST["STORE"]) {
        $row["STORE"] = $_REQUEST["STORE"];
    }
    if (isset($_REQUEST["COUNTER"]) && $_REQUEST["COUNTER"]) {
        $row["COUNTER"] = $_REQUEST["COUNTER"];
    }
    if (isset($_REQUEST["MANAGER"]) && $_REQUEST["MANAGER"]) {
        $row["MANAGER"] = $_REQUEST["MANAGER"];
    }
    if (isset($_REQUEST["SITE_ID"]) && $_REQUEST["SITE_ID"]) {
        $row["SITE_ID"] = $_REQUEST["SITE_ID"];
    }
    if (isset($_REQUEST["PRICE_VALUE_TYPE"]) && $_REQUEST["PRICE_VALUE_TYPE"]) {
        $row["PRICE_VALUE_TYPE"] = $_REQUEST["PRICE_VALUE_TYPE"];
    }
    if (isset($_REQUEST["PRICE_VALUE"]) && $_REQUEST["PRICE_VALUE"]) {
        $row["PRICE_VALUE"] = $_REQUEST["PRICE_VALUE"];
    }
    if (isset($_REQUEST["LOCATION"]) && $_REQUEST["LOCATION"]) {
        $row["LOCATION"] = $_REQUEST["LOCATION"];
    }
}


if ($_REQUEST['point_map_yandex_sotbit_regions_code_1__n0_lat']) {
    $mapY = [];
    $val = '';
    foreach ($_REQUEST as $k => $v) {
        if (mb_strpos($k, 'point_map_yandex_sotbit_regions') !== false
            && mb_strpos($k, 'lat') !== false
            && $v
        ) {
            $val = $v.',';
        }
        if (mb_strpos($k, 'point_map_yandex_sotbit_regions') !== false
            && mb_strpos($k, 'lon') !== false
            && $v
        ) {
            $val .= $v;
            $mapY[] = ['VALUE' => $val, 'DESCRIPTION' => ''];
            $val = '';
        }
    }
    $row["MAP_YANDEX"] = $mapY;
    $row["MAP_YANDEX"]['API_KEY'] = $_REQUEST['YANDEX_API_KEY'];
    $row["MAP_YANDEX"]['MARKER'] = $_REQUEST['YANDEX_MARKER'];
}

if ($_REQUEST['point_map_google_sotbit_regions_code2__n0_lat']) {
    $mapG = [];
    $val = '';
    foreach ($_REQUEST as $k => $v) {
        if (mb_strpos($k, 'point_map_google_sotbit_regions') !== false
            && mb_strpos($k, 'lat') !== false
            && $v
        ) {
            $val = $v.',';
        }
        if (mb_strpos($k, 'point_map_google_sotbit_regions') !== false
            && mb_strpos($k, 'lon') !== false
            && $v
        ) {
            $val .= $v;
            $mapG[] = ['VALUE' => $val, 'DESCRIPTION' => ''];
            $val = '';
        }
    }
    $row["MAP_GOOGLE"] = ['VALUE' => $mapG, 'API_KEY' => $_REQUEST['GOOGLE_API_KEY'], 'MARKER' => $_REQUEST['GOOGLE_MARKER']];
}


if ($_REQUEST['ID'] > 0) {
    $APPLICATION->SetTitle(
        Loc::getMessage(
            \SotbitRegions::moduleId.'_EDIT',
            [
                '#NAME#' => $row['NAME'],
            ]
        )
    );
} else {
    $APPLICATION->SetTitle(Loc::getMessage(\SotbitRegions::moduleId.'_NEW'));
}

// form
$aTabs = [
    [
        'DIV'   => 'edit1',
        'TAB'   => Loc::getMessage(\SotbitRegions::moduleId.'_REGION'),
        'ICON'  => 'ad_contract_edit',
        'TITLE' => Loc::getMessage(\SotbitRegions::moduleId.'_REGION'),
    ],
    [
        'DIV'   => 'edit4',
        'TAB'   => Loc::getMessage(\SotbitRegions::moduleId.'_REGION_MAPS'),
        'ICON'  => 'ad_contract_edit',
        'TITLE' => Loc::getMessage(\SotbitRegions::moduleId.'_REGION_MAPS'),
    ],
    [
        'DIV'   => 'edit2',
        'TAB'   => Loc::getMessage(\SotbitRegions::moduleId.'_REGION_PRICE'),
        'ICON'  => 'ad_contract_edit',
        'TITLE' => Loc::getMessage(\SotbitRegions::moduleId.'_REGION_PRICE'),
    ],
    [
        'DIV'   => 'edit3',
        'TAB'   => Loc::getMessage(\SotbitRegions::moduleId.'_LOCATION_TAB'),
        'ICON'  => 'ad_contract_edit',
        'TITLE' => Loc::getMessage(\SotbitRegions::moduleId.'_LOCATION_TAB'),
    ],
];

$tabControl = new CAdminForm('sotbit_regions_edit', $aTabs);

// delete action
if ($action == 'delete') {
    $rs = FieldsTable::getList(['filter' => ['ID_REGION' => $ID]]);
    while ($field = $rs->fetch()) {
        FieldsTable::delete($field['ID']);
    }
    $rs = LocationsTable::getList(['filter' => ['REGION_ID' => $ID]]);
    while ($field = $rs->fetch()) {
        LocationsTable::delete($field['ID']);
    }
    RegionsTable::delete($ID);
    LocalRedirect('sotbit_regions.php?site='.$site.'&lang='.LANGUAGE_ID.'&site='.$_REQUEST['site']);
}

if ($action == 'copy' && $_REQUEST['ID'] > 0) {
    $row = RegionsTable::getById($_REQUEST['ID'])->fetch();
    unset($row['ID']);
    $result = RegionsTable::add($row);
    $id = $result->getId();
    if ($id > 0) {
        $rs = FieldsTable::getList(['filter' => ['ID_REGION' => $ID]]);
        while ($field = $rs->fetch()) {
            FieldsTable::add([
                'ID_REGION' => $id,
                'CODE'      => $field['CODE'],
                'VALUE'     => $field['VALUE'],
            ]);
        }
        $rs = LocationsTable::getList(['filter' => ['REGION_ID' => $ID]]);
        while ($field = $rs->fetch()) {
            LocationsTable::add([
                'REGION_ID'   => $id,
                'LOCATION_ID' => $field['LOCATION_ID'],
            ]);
        }
        LocalRedirect('sotbit_regions_edit.php?ID='.$id.'&lang='.LANGUAGE_ID.'&site='.$_REQUEST['site']);
    }
}

// save action
if ((mb_strlen($save) > 0 || mb_strlen($apply) > 0) && $REQUEST_METHOD == 'POST'
    && check_bitrix_sessid()
) {
    $data = [];

    $USER_FIELD_MANAGER->EditFormAddFields(ENTITY_ID, $data);

    if (!is_array($PRICE_CODE)) {
        $PRICE_CODE = [];
    }
    if (!is_array($STORE)) {
        $STORE = [];
    }

    if ($data) {
        foreach ($data as &$value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (mb_strlen($v) == 0 && !is_array($v)) {
                        unset($value[$k]);
                    }
                }
            }
        }
    }

    $arFields = [
        'CODE'             => $CODE,
        'NAME'             => $NAME,
        'DEFAULT_DOMAIN'   => (empty($DEFAULT_DOMAIN) || $DEFAULT_DOMAIN == "N") ? "N" : "Y",
        'SORT'             => $SORT,
        'SITE_ID'          => $SITE_ID,
        'PRICE_CODE'       => $PRICE_CODE,
        'STORE'            => $STORE,
        'MAP_YANDEX'       => $row["MAP_YANDEX"],
        'MAP_GOOGLE'       => $row["MAP_GOOGLE"],
        'COUNTER'          => $COUNTER,
        'MANAGER'          => $MANAGER,
        'PRICE_VALUE_TYPE' => $PRICE_VALUE_TYPE,
        'PRICE_VALUE'      => $PRICE_VALUE,
    ];

    /** @param Bitrix\Main\Entity\AddResult $result */
    if ($ID > 0) {
        $result = RegionsTable::update($ID, $arFields);
    } else {
        $result = RegionsTable::add($arFields);
        $ID = $result->getId();
    }

    if ((!isset($_REQUEST['DEFAULT_DOMAIN']) || $_REQUEST['DEFAULT_DOMAIN'] == 'N') && empty($arrKeyClean)
        && !empty($allRegions) && !$issetDefaultDomain
    ) {
        $allRegions[0]['DEFAULT_DOMAIN'] = 'Y';
        $result = RegionsTable::update($allRegions[0]['ID'], $allRegions[0]);
        if (!$result->isSuccess()) {
            $errors = $result->getErrorMessages();
        }
    }

    if ($ID > 0 && !empty($data)) {

        // Get type file fields
        $arUserFieldsTypeFile = [];
        $arUserFieldsType = $USER_FIELD_MANAGER->GetUserFields(ENTITY_ID);
        foreach ($arUserFieldsType as $k => $v) {
            if ($v['USER_TYPE']['USER_TYPE_ID'] == $USER_FIELD_MANAGER::BASE_TYPE_FILE) {
                $arUserFieldsTypeFile[$k]['MULTIPLE'] = $v['MULTIPLE'];
            }
        }
        unset($arUserFieldsType);


        foreach ($data as $key => &$value) {

            if (is_array($value)) {

                // File field
                if (isset($arUserFieldsTypeFile[$key])) {

                    // multi
                    if ($arUserFieldsTypeFile[$key]['MULTIPLE'] == "Y") {

                        $arImg = [];
                        foreach ($value as $img) {

                            $img['old_file'] = $img['old_id'];
                            if (is_array($img) && ($img['old_file'] || $img['tmp_name'])) {
                                // not isset file
                                if ($img['tmp_name']) {
                                    $arImg[] = \CFile::SaveFile($img, MODULE_ID);
                                } else {
                                    // isset file
                                    if ($img['del'] == "Y") {
                                        CFile::DoDelete($img['old_file']);
                                    } else {
                                        $arImg[] = $img['old_file'];
                                    }
                                }

                            }
                        }
                        $value = serialize($arImg);

                        // not multi
                    } else {

                        $value['old_file'] = $value['old_id'];
                        // not isset file
                        if ($value['tmp_name']) {
                            $value = \CFile::SaveFile($value, MODULE_ID);
                        } else {
                            // isset file
                            if ($value['del'] == "Y") {
                                CFile::DoDelete($value['old_file']);
                                $value = '';
                            } else {
                                $value = $value['old_file'];
                            }
                        }
                    }

                    // Other field
                } else {
                    $value = serialize(array_values($value));
                }
            }

            $rs = FieldsTable::getList([
                'filter' => [
                    'CODE'      => $key,
                    'ID_REGION' => $ID,
                ],
            ]);
            while ($field = $rs->fetch()) {
                FieldsTable::update($field['ID'], ['VALUE' => $value]);
                unset($data[$key]);
            }
        }

        if ($data) {
            foreach (array_keys($data) as $code) {
                FieldsTable::add([
                    'ID_REGION' => $ID,
                    'CODE'      => $code,
                    'VALUE'     => $data[$code],
                ]);
            }
        }
    }

    if ($ID > 0) {
        $rs = LocationsTable::getList(
            [
                'filter' => ['REGION_ID' => $ID],
                'select' => ['ID'],
            ]
        );
        while ($location = $rs->fetch()) {
            LocationsTable::delete($location['ID']);
        }
        if ($LOCATION) {
            foreach ($LOCATION as $location) {
                LocationsTable::add(['REGION_ID' => $ID, 'LOCATION_ID' => $location]);
            }
        }
    }
    unset($_SESSION[ENTITY_ID]);

    if ($result->isSuccess()) {
        if (mb_strlen($save) > 0) {
            LocalRedirect('sotbit_regions.php?lang='.LANGUAGE_ID.'&site='.$_REQUEST['site']);
        } else {
            LocalRedirect('sotbit_regions_edit.php?ID='.intval($ID).'&lang='
                .LANGUAGE_ID.'&site='.$_REQUEST['site']
                .'&'.$tabControl->ActiveTabParam());
        }
    } else {
        $errors = $result->getErrorMessages();

        // rewrite values
        foreach ($data as $k => $v) {
            if (isset($row[$k])) {
                $row[$k] = $v;
            }
        }
    }

}

// menu
$aMenu = [
    [
        'TEXT'  => Loc::getMessage(\SotbitRegions::moduleId.'_RETURN'),
        'TITLE' => Loc::getMessage(\SotbitRegions::moduleId.'_RETURN'),
        'LINK'  => 'sotbit_regions.php?lang='.LANGUAGE_ID.'&site='.$_REQUEST['site'],
        'ICON'  => 'btn_list',
    ],
];

$aMenu[] = [
    'TEXT'  => Loc::getMessage(\SotbitRegions::moduleId.'_ADD'),
    'TITLE' => Loc::getMessage(\SotbitRegions::moduleId.'_ADD'),
    'LINK'  => $APPLICATION->getCurPageParam('ID=0', [
        'action',
        'ID',
    ]),
    'ICON'  => 'btn_new',
];

$aMenu[] = [
    'TEXT'  => Loc::getMessage(\SotbitRegions::moduleId.'_COPY'),
    'TITLE' => Loc::getMessage(\SotbitRegions::moduleId.'_COPY'),
    'LINK'  => $APPLICATION->getCurPageParam('action=copy', ['action']),
    'ICON'  => 'btn_copy',
];

$aMenu[] = [
    'TEXT'  => Loc::getMessage(\SotbitRegions::moduleId.'_DELETE'),
    'TITLE' => Loc::getMessage(\SotbitRegions::moduleId.'_DELETE'),
    'LINK'  => $APPLICATION->getCurPageParam('action=delete', ['action']),
    'ICON'  => 'delete',
];


$context = new CAdminContextMenu($aMenu);


require_once($_SERVER['DOCUMENT_ROOT']
    .'/bitrix/modules/main/include/prolog_admin_after.php');


if (\SotbitRegions::getDemo() == 2) {
    CAdminMessage::ShowMessage([
        "MESSAGE" => Loc::getMessage(\SotbitRegions::moduleId."_DEMO"),
        'HTML'    => true,
    ]);
}
if (\SotbitRegions::getDemo() == 3) {
    CAdminMessage::ShowMessage([
        "MESSAGE" => Loc::getMessage(\SotbitRegions::moduleId."_DEMO_END"),
        'HTML'    => true,
    ]);
}


$context->Show();


if (!empty($errors)) {
    $bVarsFromForm = true;
    CAdminMessage::ShowMessage(join("\n", $errors));
} else {
    $bVarsFromForm = false;
}

$tabControl->BeginPrologContent();

echo $USER_FIELD_MANAGER->ShowScript();

echo CAdminCalendar::ShowScript();

$tabControl->EndPrologContent();
$tabControl->BeginEpilogContent();
?>

<?= bitrix_sessid_post() ?>
    <input type="hidden" name="ID"
           value="<?= htmlspecialcharsbx(!empty($row) ? $row['ID'] : '') ?>">
    <input type="hidden" name="site" value="<?= $site ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="action" value="<?= $action ?>">

<?php $tabControl->EndEpilogContent();

$tabControl->Begin([
    'FORM_ACTION' => $APPLICATION->GetCurPage().'?ID='.IntVal($ID).'&lang='
        .LANG.'&site='.$site,
]);

$tabControl->BeginNextFormTab();


$tabControl->BeginCustomField(
    "SITE_ID",
    Loc::getMessage(\SotbitRegions::moduleId.'_SITE_ID'),
    false
);
$sites = [];
$rsSites = \Bitrix\Main\SiteTable::getList([
    'select' => [
        '*',
    ],
    'filter' => ['ACTIVE' => 'Y'],
]);
while ($site = $rsSites->fetch()) {
    $sites[$site['LID']] = '['.$site['LID'].'] '.$site['SITE_NAME'];
    if (!$row['SITE_ID'] && $site['DEF'] == 'Y') {
        $row['SITE_ID'] = [$site['LID']];
    }
}
?><input style="display: none;" type="checkbox" checked name="SITE_ID[]" value="<?= $_REQUEST['site'] ?>"><?php
$tabControl->EndCustomField("SITE_ID");


// Field: CODE
$tabControl->AddEditField(
    'CODE',
    Loc::getMessage(\SotbitRegions::moduleId.'_CODE'),
    false,
    ['size' => 50],
    $row['CODE']
);
$tabControl->BeginCustomField('CODE_NOTE', '');
?>
    <tr class="tabcontent">
    <td width="40%"></td>
    <td width="60%">
        <div class="adm-info-message-wrap">
            <div class="adm-info-message">
                <?= Loc::getMessage(\SotbitRegions::moduleId
                    .'_CODE_NOTE') ?>
            </div>
        </div>
    </td>
    </tr><?php
$tabControl->EndCustomField("CODE_NOTE");


// Field: DEFAULT_DOMAIN
$tabControl->BeginCustomField("DEFAULT_DOMAIN",
    Loc::getMessage(\SotbitRegions::moduleId.'_DEFAULT_DOMAIN'),
    false
);

?>
    <tr id="DEFAULT_DOMAIN">
    <td width="40%"><?php echo $tabControl->GetCustomLabelHTML(); ?></td>
    <td width="60%">
        <input
                type="checkbox" <?= $row['DEFAULT_DOMAIN'] == 'Y' ? 'checked' : '' ?>
                name="DEFAULT_DOMAIN"
                value="Y">
    </td>
    </tr><?
$tabControl->EndCustomField("DEFAULT_DOMAIN");
$tabControl->BeginCustomField('DEFAULT_DOMAIN_NOTE', '');
?>
    <tr class="tabcontent">
    <td width="40%"></td>
    <td width="60%">
        <div class="adm-info-message-wrap">
            <div class="adm-info-message">
                <?= Loc::getMessage(\SotbitRegions::moduleId
                    .'_DEFAULT_DOMAIN_NOTE') ?>
            </div>
        </div>
    </td>
    </tr><?
$tabControl->EndCustomField("DEFAULT_DOMAIN_NOTE");

$tabControl->AddEditField(
    'NAME',
    Loc::getMessage(\SotbitRegions::moduleId.'_NAME'),
    false,
    ['size' => 50],
    $row['NAME']
);
$tabControl->BeginCustomField('NAME_NOTE', '');
?>
    <tr class="tabcontent">
    <td width="40%"></td>
    <td width="60%">
        <div class="adm-info-message-wrap">
            <div class="adm-info-message">
                <?= Loc::getMessage(\SotbitRegions::moduleId
                    .'_NAME_NOTE') ?>
            </div>
        </div>
    </td>
    </tr><?php
$tabControl->EndCustomField("NAME_NOTE");


// Field: SORT
$tabControl->AddEditField(
    'SORT',
    Loc::getMessage(\SotbitRegions::moduleId.'_SORT'),
    false,
    ['size' => 5],
    ($row['SORT'] > 0) ? $row['SORT'] : 100
);
if (Loader::includeModule('catalog')) {
    $tabControl->BeginCustomField(
        "PRICE_CODE",
        Loc::getMessage(\SotbitRegions::moduleId.'_PRICE_CODE'),
        false
    );
    $priceCodes = [];
    $rs = CCatalogGroup::GetList([], ['ACTIVE' => 'Y']);
    while ($priceCode = $rs->fetch()) {
        $priceCodes['REFERENCE_ID'][$priceCode['ID']] = $priceCode['NAME'];
        $priceCodes['REFERENCE'][$priceCode['ID']] = '['.$priceCode['NAME'].'] '
            .$priceCode['NAME_LANG'];
    }
    ?>
    <tr id="PRICE_CODE">
    <td width="40%"><?php echo $tabControl->GetCustomLabelHTML(); ?></td>
    <td width="60%">
        <?php
        echo SelectBoxMFromArray(
            "PRICE_CODE[]",
            $priceCodes,
            $row['PRICE_CODE'],
            "",
            false,
            4,
            'style="min-width:200px"'
        );
        ?>
    </td>
    </tr><?php
    $tabControl->EndCustomField("PRICE_CODE");
    $tabControl->BeginCustomField('PRICE_CODE_NOTE', '');
    ?>
    <tr class="tabcontent">
        <td width="40%"></td>
        <td width="60%">
            <div class="adm-info-message-wrap">
                <div class="adm-info-message">
                    <?= Loc::getMessage(\SotbitRegions::moduleId
                        .'_PRICE_CODE_NOTE') ?>
                </div>
            </div>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField("PRICE_CODE_NOTE");

    $tabControl->BeginCustomField(
        "STORE",
        Loc::getMessage(\SotbitRegions::moduleId.'_STORE'),
        false
    );
    $stores = [];
    $rs = \CCatalogStore::GetList(
        [],
        [
            'ISSUING_CENTER' => 'Y',
            'ACTIVE'         => 'Y',
        ],
        false,
        false,
        [
            'ID',
            'TITLE',
        ]
    );
    while ($store = $rs->fetch()) {
        $stores['REFERENCE_ID'][$store['ID']] = $store['ID'];
        $stores['REFERENCE'][$store['ID']] = $store['TITLE'];
    }
    ?>
    <tr id="STORE">
        <td width="40%">
            <?php echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td width="60%">
            <?php
            echo SelectBoxMFromArray(
                "STORE[]",
                $stores,
                $row['STORE'],
                "",
                false,
                4,
                'style="min-width:200px"'
            );
            ?>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField("STORE");
    $tabControl->BeginCustomField('STORE_NOTE', '');
    ?>
    <tr class="tabcontent">
        <td width="40%"></td>
        <td width="60%">
            <div class="adm-info-message-wrap">
                <div class="adm-info-message">
                    <?= Loc::getMessage(\SotbitRegions::moduleId
                        .'_STORE_NOTE') ?>
                </div>
            </div>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField("STORE_NOTE");
}


// Field: COUNTER
$tabControl->BeginCustomField(
    "COUNTER",
    Loc::getMessage(\SotbitRegions::moduleId.'_COUNTER'),
    false
);
?>
    <tr id="COUNTER">
        <td width="40%"><?php echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td width="60%">
            <?php
            \CFileMan::AddHTMLEditorFrame(
                "COUNTER",
                $row["COUNTER"],
                "COUNTER",
                "html",
                [
                    'height' => 150,
                    'width'  => '100%',
                ],
                "N",
                0,
                "",
                "",
                "ru"
            );
            ?>
        </td>
    </tr>
<?php
$tabControl->EndCustomField("COUNTER");


// Field: MANAGER
$tabControl->BeginCustomField(
    "MANAGER",
    Loc::getMessage(\SotbitRegions::moduleId.'_MANAGER'),
    false
);
?>
    <tr id="MANAGER">
        <td width="40%"><?php echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td width="60%">
            <?php
            echo FindUserID(
                "MANAGER",
                $row["MANAGER"],
                "",
                "sotbit_regions_edit_form",
                4
            );
            ?>
        </td>
    </tr>
<?php $tabControl->EndCustomField("MANAGER");


//$ufields = $USER_FIELD_MANAGER->GetUserFields(ENTITY_ID);
//$hasSomeFields = !empty($ufields);

//remove files for copy action
/*if ($hasSomeFields && !empty($row)) {
    foreach ($ufields as $ufCode => $ufField) {
        if (isset($ufField['USER_TYPE_ID'])
            && $ufField['USER_TYPE_ID'] == 'file'
            || (
                isset($ufField['USER_TYPE']) && is_array($ufField['USER_TYPE'])
                && isset($ufField['USER_TYPE']['BASE_TYPE'])
                && $ufField['USER_TYPE']['BASE_TYPE'] == 'file'
            )
        ) {
            $row[$ufCode] = null;
        }
    }
}*/

echo $tabControl->ShowUserFieldsWithReadyData(
    ENTITY_ID,
    $row,
    $bVarsFromForm,
    'ID'
);

// Field: MAP_YANDEX
$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField(
    "MAP_YANDEX",
    Loc::getMessage(\SotbitRegions::moduleId.'_MAP_YANDEX'),
    false
);
?>
    <tr class="heading">
        <td><?= Loc::getMessage(\SotbitRegions::moduleId
                .'_REGION_MAPS_YANDEX_TITLE') ?></td>
    </tr>
    <tr id="MAP_YANDEX">
        <td>
            <table cellpadding="0" cellspacing="0" border="0" class="nopadding"
                   width="100%" id="MAP_YANDEX1">
                <tbody>
                <?php
                for ($i = 0; $i < 1; ++$i) {
                    ?>
                    <tr>
                        <td>
                            <?php
                            echo call_user_func_array(
                                [
                                    'CIBlockPropertyMapYandex',
                                    'GetPropertyFieldHtml',
                                ],
                                [
                                    [
                                        'MULTIPLE'           => 'N',
                                        'ID'                 => 1,
                                        'CODE'               => 'sotbit_regions_code',
                                        'USER_TYPE_SETTINGS' => ['API_KEY' => $row["MAP_YANDEX"]['API_KEY']],
                                    ],
                                    ['VALUE' => $row["MAP_YANDEX"][$i]['VALUE']],
                                    [
                                        "VALUE"     => $row["MAP_YANDEX"][$i]['VALUE'],
                                        "FORM_NAME" => 'sotbit_regions_edit_form',
                                        "MODE"      => "FORM_FILL",
                                        "COPY"      => false,
                                    ],
                                ]
                            );
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>

            <?= Loc::getMessage(\SotbitRegions::moduleId.'_YANDEX_API_KEY') ?>
            <input type="text" name="YANDEX_API_KEY"
                   value="<?= $row["MAP_YANDEX"]['API_KEY'] ?>">
            <?= Loc::getMessage(\SotbitRegions::moduleId.'_REGION_MAPS_YANDEX_API') ?>
            <br>


            <?= Loc::getMessage(\SotbitRegions::moduleId.'_REGION_MAPS_MARKER') ?>
            <?$mapsMarker = new Sotbit\Regions\Config\Widgets\File(
                'YANDEX_MARKER',
                [
                    'SITE_ID'    => $_REQUEST['site'],
                    'default'    => $row["MAP_YANDEX"]['MARKER'],
                    'preview'    => true,
                ]
            );
            $mapsMarker->show();
            ?>

        </td>
    </tr>
<?php
$tabControl->EndCustomField("MAP_YANDEX");


// Field: MAP_GOOGLE
$tabControl->BeginCustomField(
    "MAP_GOOGLE",
    Loc::getMessage(\SotbitRegions::moduleId.'_MAP_GOOGLE'),
    false
);
?>
    <tr class="heading">
        <td><?= Loc::getMessage(\SotbitRegions::moduleId
                .'_REGION_MAPS_GOOGLE_TITLE') ?></td>
    </tr>
    <tr id="MAP_GOOGLE">
        <td>
            <table cellpadding="0" cellspacing="0" border="0" class="nopadding"
                   width="100%" id="MAP_GOOGLE1">
                <tbody>
                <?php
                for ($i = 0; $i < 1; ++$i) {
                    ?>
                    <tr>
                        <td>
                            <?php
                            echo call_user_func_array(
                                [
                                    'CIBlockPropertyMapGoogle',
                                    'GetPropertyFieldHtml',
                                ],
                                [
                                    [
                                        'MULTIPLE'           => 'N',
                                        'ID'                 => 2,
                                        'CODE'               => 'sotbit_regions_code',
                                        'USER_TYPE_SETTINGS' => ['API_KEY' => $row["MAP_GOOGLE"]['API_KEY']],
                                    ],
                                    ['VALUE' => $row["MAP_GOOGLE"]['VALUE'][$i]['VALUE']],
                                    [
                                        "VALUE"     => $row["MAP_GOOGLE"]['VALUE'][$i]['VALUE'],
                                        "FORM_NAME" => 'sotbit_regions_edit_form',
                                        "MODE"      => "FORM_FILL",
                                        "COPY"      => false,
                                    ],
                                ]
                            );
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?= Loc::getMessage(\SotbitRegions::moduleId.'_GOOGLE_API_KEY') ?>
            <input type="text" name="GOOGLE_API_KEY"
                   value="<?= $row["MAP_GOOGLE"]['API_KEY'] ?>">
            <?= Loc::getMessage(\SotbitRegions::moduleId.'_REGION_MAPS_GOOGLE_API') ?>
            <br>


            <?= Loc::getMessage(\SotbitRegions::moduleId.'_REGION_MAPS_MARKER') ?>
            <?$mapsMarker = new Sotbit\Regions\Config\Widgets\File(
                'GOOGLE_MARKER',
                [
                    'SITE_ID'    => $_REQUEST['site'],
                    'default'    => $row["MAP_GOOGLE"]['MARKER'],
                    'preview'    => true,
                ]
            );
            $mapsMarker->show();
            ?>
        </td>
    </tr>
<?php $tabControl->EndCustomField("MAP_GOOGLE");




$tabControl->BeginNextFormTab();

// Field: REGION_PRICE
$tabControl->BeginCustomField(
    "REGION_PRICE",
    Loc::getMessage(\SotbitRegions::moduleId.'_REGION_PRICE'),
    false
);

$arValueTypeSel["REFERENCE_ID"] = [
    '0',
    'PROCENT_UP',
    'PROCENT_DOWN',
    'FIX_UP',
    'FIX_DOWN',
];
$arValueTypeSel["REFERENCE"] = [
    '-',
    Loc::getMessage(\SotbitRegions::moduleId."_PROCENT_UP"),
    Loc::getMessage(\SotbitRegions::moduleId."_PROCENT_DOWN"),
    Loc::getMessage(\SotbitRegions::moduleId."_FIX_UP"),
    Loc::getMessage(\SotbitRegions::moduleId."_FIX_DOWN"),
];
?>
    <tr class="heading">
        <td colspan="2"><?= Loc::getMessage(\SotbitRegions::moduleId
                .'_REGION_PRICE_TITLE') ?></td>
    </tr>
    <tr id="tr_VALUE_TYPE" class="tabcontent ChangeValue">
        <td width="40%" class="left-side-wrapper top-left-side-wrapper">
            <div
                    class="left-side top-left-side"><?php echo $tabControl->GetCustomLabelHTML(); ?></div>
        </td>
        <td width="60%" class="right-side-wrapper top-right-side-wrapper">
            <div
                    class="right-side top-right-side">
                <?php
                echo SelectBoxFromArray(
                    'PRICE_VALUE_TYPE',
                    $arValueTypeSel,
                    $row['PRICE_VALUE_TYPE'],
                    '',
                    'style="min-width:320px;"',
                    false,
                    ''
                );
                ?>
            </div>
        </td>
    </tr>
    <tr id="tr_VALUE" class="tabcontent ChangeValue">
        <td width="40%" class="left-side-wrapper bottom-left-side-wrapper">
            <div class="left-side bottom-left-side">
                <?= Loc::getMessage(\SotbitRegions::moduleId.'_PRICE_VALUE') ?>
            </div>
        </td>
        <td width="60%" class="right-side-wrapper bottom-right-side-wrapper">
            <div class="right-side bottom-right-side">
                <input type="text" name="PRICE_VALUE"
                       value="<?= $row['PRICE_VALUE'] ?>"
                       size="25" maxlength="25" style="width: 310px;">
            </div>
        </td>
    </tr>
<?php $tabControl->EndCustomField("REGION_PRICE");


// Field: LOCATION
if (Loader::includeModule('sale')) {
    $tabControl->BeginNextFormTab();
    $tabControl->BeginCustomField(
        "LOCATION",
        Loc::getMessage(\SotbitRegions::moduleId.'_LOCATION_ID'),
        false
    );

    ?>

    <tr id="tr_VALUE" class="tabcontent LocationValue">
        <td width="40%" class="left-side-wrapper bottom-left-side-wrapper">
            <div class="left-side bottom-left-side">
                <?= Loc::getMessage(\SotbitRegions::moduleId.'_LOCATION') ?>
            </div>
        </td>
        <td width="60%" class="right-side-wrapper bottom-right-side-wrapper">
            <div class="right-side bottom-right-side">
                <div class="location-border" id="location-border"
                     style="<?= (!$row['LOCATION_ID']) ? 'display:none;'
                         : 'display:block;' ?>">
                    <?php
                    if ($row['LOCATION_ID']) {
                        foreach ($row['LOCATION_ID'] as $location) {
                            ?>
                            <span class="location-item">
								<input type="hidden" name="LOCATION[]"
                                       value="<?= $location ?>">
								<span onclick="showPopupLocation(<?= $location ?>)">
									[<?= $location ?>]
									<?= \Bitrix\Sale\Location\Admin\LocationHelper::getLocationPathDisplay($location) ?>
								</span>
								<span class="location-item-delete"
                                      onclick="deleteItem('<?= $location ?>')">x</span>
							</span>
                            <?
                        }
                    }
                    ?>
                </div>

                <span class="location-button location-plus"
                      onclick="showPopupLocation(0)">
					<?= Loc::getMessage(SotbitRegions::moduleId
                        .'_LOCATION_ADD') ?>
				</span>

                <div id="modalLocation" class="modal">
                    <div class="modal-content">
                        <div id="modal-content-form"></div>
                        <?
                        $input = [
                            'TYPE'           => 'LOCATION',
                            'SIZE'           => 40,
                            'IS_SEARCH_LINE' => true,
                        ];
                        echo Input\Manager::getEditHtml('LOCATION_TYPE', $input); ?>
                        <div class="location-buttons">
							<span class="location-save" id="locationSave">
								<?= Loc::getMessage(SotbitRegions::moduleId
                                    .'_LOCATION_SAVE') ?>
							</span>
                            <span class="location-close"
                                  onclick="closePopupLocation()">
								<?= Loc::getMessage(SotbitRegions::moduleId
                                    .'_LOCATION_CLOSE') ?>
							</span>
                        </div>
                    </div>
                </div>


                <script>
                    $(document).on('click', '#locationSave', function (e) {
                        var value = $('#modalLocation').find('input[name="LOCATION_TYPE"]').val();
                        if (value.length == 10 || value.length == 3) {
                            $.ajax({
                                url: '/bitrix/admin/sotbit_regions_ajax.php',
                                cache: false,
                                data: {code: value, action: 'showName'},
                                success: function (answer) {
                                    answer = JSON.parse(answer);
                                    if ($('#location-border').find('input[value="' + answer['ID'] + '"]').length == 0) {
                                        $('#location-border').show();
                                        $('#location-border').append('' +
                                            '<span class="location-item">' +
                                            '<input type="hidden" name="LOCATION[]" value="' + answer['ID'] + '">[' + answer['ID'] + '] ' + answer['NAME'] + '' +
                                            '<span class="location-item-delete" onclick="deleteItem(\'' + answer['ID'] + '\')">x</span></span>');
                                    }
                                }
                            });
                        }
                    });
                    $(document).on('click', '#modalLocation', function (e) {
                        if (e.target != this) return;
                        closePopupLocation();
                    });

                    function showPopupLocation(id) {
                        closePopupLocation();
                        var modal = document.getElementById('modalLocation');
                        modal.style.display = "block";
                    }

                    function closePopupLocation() {
                        var modal = document.getElementById('modalLocation');
                        modal.style.display = "none";
                    }

                    function deleteItem(id) {
                        $('#location-border').find('input[value="' + id + '"]').closest('.location-item').remove();
                        if ($('#location-border .location-item').length == 0) {
                            $('#location-border').hide();
                        }
                    }
                </script>

                <style>
                    .location-item {
                        display: inline-block;
                        padding: 4px 8px 4px 8px;
                        box-shadow: inset 0 1px 0 #f3f6e4;
                        -webkit-box-shadow: inset 0 1px 0 #f3f6e4;
                        border: 1px solid #b4bd98;
                        border-radius: 2px;
                        background-color: #dfe8bc;
                        background-image: -webkit-linear-gradient(top, #e6ecc9, #d6e1a9);
                        background-image: -moz-linear-gradient(top, #e6ecc9, #d6e1a9);
                        background-image: -o-linear-gradient(top, #e6ecc9, #d6e1a9);
                        background-image: linear-gradient(top, #e6ecc9, #d6e1a9);
                        margin-right: 5px;
                    }

                    .location-item-delete {
                        font-weight: bold;
                        cursor: pointer;
                        margin-left: 5px;
                    }

                    .location-border {
                        border: 1px solid #caced7;
                        padding: 10px;
                        background-color: #fdfefe;
                    }

                    .location-buttons {
                        margin-top: 10px;
                        display: flex;
                        justify-content: center;
                    }

                    .location-save {
                        cursor: pointer;
                        text-align: center;
                        background: #70bb18;
                        border: 0;
                        color: #fff;
                        font-size: 13px;
                        position: relative;
                        border-radius: 2px;
                        padding: 10px;
                        margin-right: 10px;
                    }

                    .location-close {
                        cursor: pointer;
                        text-align: center;
                        background: #f1361b;
                        border: 0;
                        color: #fff;
                        font-size: 13px;
                        position: relative;
                        border-radius: 2px;
                        padding: 10px;
                    }

                    .location-plus {
                        color: #113c7d;
                        text-decoration: none;
                        cursor: pointer;
                        border-bottom: 1px dashed #113c7d;
                    }

                    .modal {
                        display: none;
                        position: fixed;
                        z-index: 999;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        overflow: auto;
                        background-color: rgb(0, 0, 0);
                        background-color: rgba(0, 0, 0, 0.4);
                    }

                    .modal-content {
                        background-color: #fefefe;
                        margin: 15% 0 15% -125px;
                        padding: 20px;
                        border: 1px solid #888;
                        display: inline-block;
                        width: 250px;
                        position: absolute;
                        left: 50%;
                    }

                    .close {
                        color: #aaa;
                        float: right;
                        font-size: 28px;
                        font-weight: bold;
                    }

                    .close:hover,
                    .close:focus {
                        color: black;
                        text-decoration: none;
                        cursor: pointer;
                    }

                </style>
            </div>
        </td>
    </tr>
    <?
}
$tabControl->EndCustomField("LOCATION");

$tabControl->Buttons([
    'disabled' => false,
    'back_url' => 'sotbit_regions.php?lang='.LANGUAGE_ID.'&site='.$_REQUEST['site'],
]);

$tabControl->Show();
?>
    </form>
<?php

if ($_REQUEST['mode'] == 'list') {
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_js.php');
} else {
    if (Loader::includeModule('sale')) {
        require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
    }
}
?>