<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
$moduleId='sotbit.regions';
$arComponentDescription = array(
	"NAME" => Loc::getMessage($moduleId."_DELIVERY_NAME"),
	"DESCRIPTION" => Loc::getMessage($moduleId."_DELIVERY_DESCRIPTION"),
	"ICON" => "",
	"SORT" => 70,
	"PATH" => array(
		"ID" => "sotbit",
        "NAME" => Loc::getMessage("SOTBIT_COMPONENTS_TITLE"),
		"CHILD" => array(
			"ID" => "regions",
			"NAME" => Loc::getMessage($moduleId."_DELIVERY_COMMENTS"),
		),
	),
);
?>