<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
        "ELEMENT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SOTBIT_REGIONS_DELIVERY_ELEMENT_ID"),
            "TYPE" => "TEXT",
        ),
        "LIMIT" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SOTBIT_REGIONS_DELIVERY_LIMIT"),
            "TYPE" => "TEXT",
        ),
        "AJAX" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SOTBIT_REGIONS_DELIVERY_AJAX"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y"
        ),
        "START_AJAX" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SOTBIT_REGIONS_DELIVERY_START_AJAX"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N"
        ),
	),
);
?>