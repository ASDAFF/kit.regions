<?php
define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main\Loader;
use Sotbit\Regions\Internals;

require_once($_SERVER["DOCUMENT_ROOT"]
    ."/bitrix/modules/main/include/prolog_before.php");

if (!Loader::includeModule('sotbit.regions') || !Loader::includeModule('sale') || !Loader::includeModule('main')
) {
    return false;
}
ob_start();
switch ($action)
{
    case 'getDomainByLocation':
        $Location = new \Sotbit\Regions\System\Location();
        $region = $Location->findRegionByIdLocation($id);
        if (!$region && !empty($id)) {
            $Region = new \Sotbit\Regions\Internals\RegionsTable();
            $region = $Region->getById($_REQUEST['id'])->fetch();
        }
        if(!$region)
        {
            $Region = new \Sotbit\Regions\Region();
            $region = $Region->setSomeRegion();
        }

        echo \Bitrix\Main\Web\Json::encode($region);
        break;

    case 'getMultiDomainByLocation':
        $Location = new \Sotbit\Regions\System\Location();
        $region = $Location->findRegionByIdLocation($id);
        if(!$region)
        {
            $Region = new \Sotbit\Regions\Region();
            $region = $Region->setSomeRegion();
        }
        echo \Bitrix\Main\Web\Json::encode($region);
        break;
}
die();
?>