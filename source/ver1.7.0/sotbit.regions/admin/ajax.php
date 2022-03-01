<?php

use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\Input;
use Bitrix\Main\Web\Json;

require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/bitrix/modules/main/include/prolog_admin_before.php"
);
try {
    if (!Loader::includeModule('sotbit.regions')
        || !Loader::includeModule('sale')
    ) {
        return false;
    }
} catch (\Bitrix\Main\LoaderException $e) {
    print_r($e->getMessage());
}


if ($action == 'showName') {
    if ($code) {
        $return = ['ID' => '', 'NAME' => ''];
        $location = \Bitrix\Sale\Location\LocationTable::getList(
            ['filter' => ['CODE' => $code]]
        )->fetch();
        if ($location['ID'] > 0) {
            $return = [
                'ID'   => $location['ID'],
                'NAME' => \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById($location['ID']),
            ];
        }
        echo Json::encode($return);
    }
}
if ($action == 'showForm') {
    if ($id > 0) {
        $return = ['ID' => '', 'NAME' => ''];
        $location = \Bitrix\Sale\Location\LocationTable::getList(
            ['filter' => ['ID' => $id]]
        )->fetch();
    }
    $input = [
        'TYPE' => 'LOCATION',
        'SIZE' => 40,
    ];
    if ($location['CODE']) {
        $input['VALUE'] = $location['CODE'];
    }
    echo Input\Manager::getEditHtml('LOCATION_TYPE', $input);
}

?>