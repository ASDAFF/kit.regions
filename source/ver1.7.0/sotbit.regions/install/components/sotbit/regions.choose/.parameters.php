<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
        "FROM_LOCATION" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SOTBIT_REGIONS_FROM_LOCATION"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
	),
);
?>