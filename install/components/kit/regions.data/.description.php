<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
$moduleId='kit.regions';
$arComponentDescription = array(
	"NAME" => Loc::getMessage($moduleId."_NAME_DATA"),
	"DESCRIPTION" => Loc::getMessage($moduleId."_DESCRIPTION_DATA"),
	"ICON" => "",
	"SORT" => 80,
	"PATH" => array(
		"ID" => "kit",
		"CHILD" => array(
			"ID" => "regions",
			"NAME" => Loc::getMessage($moduleId."_COMMENTS_DATA"),
			"SORT" => 362,
		),
	),
);
?>