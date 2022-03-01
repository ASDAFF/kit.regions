<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);
Loc::loadMessages(__FILE__);

$params = [];

if (!empty($arParams['INIT_MAP_TYPE'])) {
    $params['INIT_MAP_TYPE'] = $arParams['INIT_MAP_TYPE'];
}

if (!empty($arResult['MAP_DATA'])) {
    $params['MAP_DATA'] = serialize($arResult['MAP_DATA']);
}

if (!empty($arParams['MAP_WIDTH'])) {
    $params['MAP_WIDTH'] = $arParams['MAP_WIDTH'];
}

if (!empty($arParams['MAP_HEIGHT'])) {
    $params['MAP_HEIGHT'] = $arParams['MAP_HEIGHT'];
}

if (!empty($arParams['CONTROLS'])) {
    $params['CONTROLS'] = $arParams['CONTROLS'];
}

if (!empty($arParams['OPTIONS'])) {
    $params['OPTIONS'] = $arParams['OPTIONS'];
}

if (!empty($arParams['API_KEY'])) {
    $params['API_KEY'] = $arParams['API_KEY'];
}

if (!empty($arResult['MARKER'])) {
    $params['MARKER'] = $arResult['MARKER'];
}

switch ($arParams['TYPE']) {
    case 'yandex':
        $APPLICATION->IncludeComponent(
            "bitrix:map.yandex.view",
            "sotbit_regions",
            $params,
            false
        );
        break;
    case 'google':
        $APPLICATION->IncludeComponent(
            "bitrix:map.google.view",
            "sotbit_regions",
            $params
        );
        break;
    default:
}
