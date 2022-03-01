<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 30-Jan-18
 * Time: 2:00 PM
 */
namespace Sotbit\Regions\UserType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
class Html
{
	const USER_TYPE_ID = "html";
	public function OnUserTypeBuildListHandler()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => Loc::getMessage(\SotbitRegions::moduleId."_HTML_DESCRIPTION"),
			"BASE_TYPE" => static::USER_TYPE_ID,
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
		);
	}
	function GetDBColumnType($arUserField)
	{
		return "text";
	}
	public function getEntityField($fieldName, $fieldParameters)
	{
		return new Entity\TextField($fieldName, $fieldParameters);
	}
	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		if (!Loader::includeModule("fileman"))
			return Loc::getMessage("IBLOCK_PROP_HTML_NOFILEMAN_ERROR");

		Loader::includeModule('fileman');

		ob_start();

		\CFileMan::AddHTMLEditorFrame(
			$arHtmlControl["NAME"],
			$arHtmlControl["VALUE"],
            $arHtmlControl["NAME"],
			'html',
			array(
				'height' => 450,
				'width' => '100%'
			),
			"N",
			0,
			"",
			"",
			'',
			true,
			false,
			array(
				'toolbarConfig' => 'admin',
			)
		);
		$s = ob_get_contents();
		ob_end_clean();
		return  $s;
	}

	function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
	{

	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		return '';
	}
}