<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Config;
use Sotbit\Regions\Helper\LocationType;
use Bitrix\Sale\Location\LocationTable;
use Sotbit\Regions\Controllers\AdminController;
use Bitrix\Main\ORM\Fields\ExpressionField;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');

Loc::loadMessages(__FILE__);


$saleIsInstaled = Loader::includeModule('sale');

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
    ['NOTE' => str_replace(
        '######', AdminController::getUrl('downloadFile', ['name' => 'update.log']), Loc::getMessage(SotbitRegions::moduleId.'_WIDGET_FIND_USER_METHOD_NOTE')
    )]);

$findUserMethodValues = ['services' => Loc::getMessage(SotbitRegions::moduleId.'_SERVICES')];

if (Loader::includeModule('statistic')) {
    $findUserMethodValues['statistic'] = Loc::getMessage(SotbitRegions::moduleId.'_STATISTIC');
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



if ($saleIsInstaled) {
    /**
     * Tab: ADD LANG
     */

    /** @var iterable $contrys */
    $contrys = LocationTable::query()
        ->addSelect('ID')
        ->addSelect('NAME.NAME')
        ->where(new ExpressionField('1',  'CAST(%s as UNSIGNED)', ['NAME.NAME']), 0)
        ->where(new ExpressionField('2',  'LENGTH(%s)', ['NAME.NAME']), '>', 2)
        ->where('PARENT_ID', 0)
        ->fetchCollection();

    $Tab = new Config\Tab('3');
    $charset = new Config\Widgets\Select('CHARSET_UPLOD_FILE');
    $charset->setValues(['windows-1251' => '', 'utf-8' => '']);
    $Group = new Config\Group('DOWNLOAD_FILES_FOR_ADD_LANGS');
    $contrysNames = [];
    foreach ($contrys as $contry) {
        $contrysNames[$contry->get('ID')] = $contry->get('NAME')->get('NAME');
        $checkBox = new Config\Widgets\CheckBox($contry->get('NAME')->get('NAME'));
        $Group->getWidgets()->addItem($checkBox);
    }
    $upload = new Config\Widgets\AnyElement('UPLOAD_CSV_FILE', [
        'path' => __DIR__ . '/uploadLangs.php',
        'url' => '/',
        'elementsId' => $contrysNames,
    ]);
    $Group->getWidgets()->addItem($charset);
    $Group->getWidgets()->addItem($upload);
    $download = new Config\Widgets\AnyElement('DOWNLOAD_NEW_LANGS', [
        'path' => __DIR__ . '/downloadLangs.php',
        'url' => '/',
        'elementsId' => $contrysNames,
        'charsetId' => 'CHARSET_UPLOD_FILE',
    ]);
    $Group->getWidgets()->addItem($download);

    $instruction = new Config\Widgets\AnyElement('ok', [
        'path' => __DIR__ . '/instruction.php',
        'CUSTOM_ROW' => true,
    ]);
    $Group->getWidgets()->addItem($instruction);

    $Tab->getGroups()->addItem($Group);
    $Options->getTabs()->addItem($Tab);

}

$Options->show();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
