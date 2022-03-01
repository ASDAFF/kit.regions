<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if(!\Bitrix\Main\Loader::includeModule('sotbit.regions')){
    return false;
}

$regions = \Sotbit\Regions\System\Location::getLocations();
$arResult['REGION_LIST_COUNTRIES'] = $regions['REGION_LIST_COUNTRIES'];

if ($arResult['DELIVERY']) {
    foreach ($arResult['DELIVERY'] as &$delivery) {
        if($delivery['LOGOTIP']) {
            $delivery['LOGOTIP'] = CFile::ResizeImageGet(
                $delivery['LOGOTIP'],
                [
                    'width'  => 125,
                    'height' => 125
                ],
                BX_RESIZE_IMAGE_PROPORTIONAL
            );
            $delivery['LOGOTIP']['SRC'] = $delivery['LOGOTIP']['src'];
        }
    }
}
if ($arResult['PAYMENT']) {
    foreach ($arResult['PAYMENT'] as &$payment) {
        if($payment['LOGOTIP']) {
            $payment['LOGOTIP'] = CFile::ResizeImageGet(
                $payment['LOGOTIP'],
                [
                    'width'  => 110,
                    'height' => 110
                ],
                BX_RESIZE_IMAGE_PROPORTIONAL
            );
            $payment['LOGOTIP']['SRC'] = $payment['LOGOTIP']['src'];
        }
    }
}