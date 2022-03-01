<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Config;
use Sotbit\Regions\Helper\LocationType;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');

Loc::loadMessages(__FILE__);


if ($APPLICATION->GetGroupRight("main") < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
if (!Loader::includeModule('sotbit.regions')) {
    return false;
}

$Options = new Config\Admin($_REQUEST['site']);

// group: SETTINGS
$oneDomain = new Config\Widgets\CheckBox('SINGLE_DOMAIN');
$modeLocation = new Config\Widgets\CheckBox('MODE_LOCATION');
$insertSaleLocation = new Config\Widgets\CheckBox('INSERT_SALE_LOCATION');
$addOrderProperty = new Config\Widgets\CheckBox(
    'ADD_ORDER_PROPERTY',
    ['NOTE' => Loc::getMessage(SotbitRegions::moduleId.'_ADD_ORDER_PROPERTY_NOTE'),]
);
$findUserMethod = new Config\Widgets\Select(
    'FIND_USER_METHOD',
    ['NOTE' => Loc::getMessage(SotbitRegions::moduleId.'_WIDGET_FIND_USER_METHOD_NOTE')]);


if (Loader::includeModule('statistic')) {
    $findUserMethodValues = [
        'ipgeobase' => 'IpGeoBase',
        'statistic' => Loc::getMessage(SotbitRegions::moduleId.'_STATISTIC'),
    ];
} else {
    $findUserMethodValues = [
        'ipgeobase' => 'IpGeoBase',
    ];
}

if (function_exists('geoip_record_by_name')) {
    $findUserMethodValues['geoip'] = 'GeoIp';
}
$findUserMethod->setValues($findUserMethodValues);

$mapsMarker = new Config\Widgets\File(
    'MAPS_MARKER',
    [
        'SITE_ID'    => $_REQUEST['site'],
        'preview'    => true,
        'NOTE'       => Loc::getMessage(SotbitRegions::moduleId.'_WIDGET_MAPS_MARKER_NOTE')
    ]
);

$mapsYandexApi = new Config\Widgets\Str('MAPS_YANDEX_API');

$mapsGoogleApi = new Config\Widgets\Str('MAPS_GOOGLE_API', [
    'NOTE' => Loc::getMessage(SotbitRegions::moduleId.'_WIDGET_MAPS_GOOGLE_API_NOTE'),
]);


// group: VARIABLES_SETTINGS
$multipleDelimiter = new Config\Widgets\Str('MULTIPLE_DELIMITER', ['COLSPAN' => [0 => 2]]);
$Variables = new Config\Widgets\Variables(
    'AVAILABLE_VARIABLES',
    [
        'CUSTOM_ROW' => true,
        'SITE_ID'    => $_REQUEST['site'],
        ]
);

$locationType = new Config\Widgets\Select('LOCATION_TYPE',
    ['NOTE' => Loc::getMessage(SotbitRegions::moduleId.'_WIDGET_LOCATION_TYPE_NOTE'),]);
$locationTypeList = LocationType::getListFormat();
if (!empty($locationTypeList)) {
    $locationType->setValues($locationTypeList);
}


/**
 * Tab: SETTING
 */
$Tab = new Config\Tab('1');

// group: MAIN_SETTINGS
$Group = new Config\Group('MAIN_SETTINGS');
$Group->getWidgets()->addItem($oneDomain);
if (Loader::includeModule('sale')) {
    $Group->getWidgets()->addItem($modeLocation);
}
$Group->getWidgets()->addItem($findUserMethod);
$Group->getWidgets()->addItem($insertSaleLocation);
$Group->getWidgets()->addItem($addOrderProperty);
if (Loader::includeModule('sale')) {
    $Group->getWidgets()->addItem($locationType);
}
$Tab->getGroups()->addItem($Group);

// group: MAPS_SETTINGS
$Group = new Config\Group('MAPS_SETTINGS');
$Tab->getGroups()->addItem($Group);
$Group->getWidgets()->addItem($mapsMarker);
$Group->getWidgets()->addItem($mapsYandexApi);
$Group->getWidgets()->addItem($mapsGoogleApi);
$Options->getTabs()->addItem($Tab);


/**
 * Tab: VARIABLES
 */
$Tab = new Config\Tab('2');

// group: VARIABLES_SETTINGS
$Group = new Config\Group('VARIABLES_SETTINGS', ['COLSPAN' => 3]);
$Group->getWidgets()->addItem($multipleDelimiter);
$Tab->getGroups()->addItem($Group);
$Group = new Config\Group('VARIABLES', ['COLSPAN' => 3]);
$Group->getWidgets()->addItem($Variables);
$Tab->getGroups()->addItem($Group);
$Options->getTabs()->addItem($Tab);


$Options->show();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>