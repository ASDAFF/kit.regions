<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!\Bitrix\Main\Loader::includeModule('sotbit.regions')){
    return;
}

$regions = [];
$rs = \Sotbit\Regions\Internals\RegionsTable::getList([]);
while($region = $rs->fetch()){
    $regions[$region['ID']] = '['.$region['ID'].'] '.$region['NAME'];
}

$fields = [];
$tags = \SotbitRegions::getTags(array(SITE_ID));
if($tags){
    foreach($tags as $tag){
        $fields[$tag['CODE']] = '['.$tag['CODE'].'] '.$tag['NAME'];
    }
}
$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
        "REGION_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SOTBIT_REGIONS_DATA_REGION_ID"),
            "TYPE" => "LIST",
            "VALUES" => $regions,
        ),
        "REGION_FIELDS" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SOTBIT_REGIONS_DATA_REGION_FIELDS"),
            "TYPE" => "LIST",
            "VALUES" => $fields,
            "MULTIPLE" => "Y",
        ),
        "CACHE_TIME" => Array("DEFAULT"=>"36000000"),
	),
);
?>