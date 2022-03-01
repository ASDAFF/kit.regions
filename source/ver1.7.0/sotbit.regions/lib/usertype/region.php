<?php

namespace Sotbit\Regions\UserType;

use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Internals\RegionsTable;

class Region extends \Bitrix\Main\UserField\TypeBase
{
    const USER_TYPE     = 'RegionsList';
    const PROPERTY_TYPE = 'R';

    public static function GetUserTypeDescription()
    {
        return [
            "PROPERTY_TYPE"             => self::PROPERTY_TYPE,
            "USER_TYPE"                 => self::USER_TYPE,
            "DESCRIPTION"               => Loc::getMessage(\SotbitRegions::moduleId
                ."_PROP_REGIONS_DESC"),
            "GetPropertyFieldHtml"      => [__CLASS__, "GetPropertyFieldHtml"],
            "GetPropertyFieldHtmlMulty" => [
                __CLASS__,
                "GetPropertyFieldHtmlMulty",
            ],
            "GetPublicEditHTML"         => [__CLASS__, "GetPropertyFieldHtml"],
            "GetPublicEditHTMLMulty"    => [
                __CLASS__,
                "GetPropertyFieldHtmlMulty",
            ],
            "GetAdminListViewHTML" => array(__CLASS__, "GetAdminListViewHTML"),
            "GetPublicViewHTML"         => [__CLASS__, "GetPublicViewHTML"],
            "GetUIFilterProperty"        => [__CLASS__, "GetUIFilterProperty"],
            "PrepareSettings"           => [__CLASS__, "PrepareSettings"],
            "GetSettingsHTML"           => [__CLASS__, "GetSettingsHTML"],
            "GetExtendedValue"          => [__CLASS__, "GetExtendedValue"],
        ];
    }
    public static function PrepareSettings($arProperty)
    {
        $size = 0;
        if (is_array($arProperty["USER_TYPE_SETTINGS"])) {
            $size = intval($arProperty["USER_TYPE_SETTINGS"]["size"]);
        }
        if ($size <= 0) {
            $size = 1;
        }

        $width = 0;
        if (is_array($arProperty["USER_TYPE_SETTINGS"])) {
            $width = intval($arProperty["USER_TYPE_SETTINGS"]["width"]);
        }
        if ($width <= 0) {
            $width = 0;
        }

        if (is_array($arProperty["USER_TYPE_SETTINGS"])
            && $arProperty["USER_TYPE_SETTINGS"]["group"] === "Y"
        ) {
            $group = "Y";
        } else {
            $group = "N";
        }

        if (is_array($arProperty["USER_TYPE_SETTINGS"])
            && $arProperty["USER_TYPE_SETTINGS"]["multiple"] === "Y"
        ) {
            $multiple = "Y";
        } else {
            $multiple = "N";
        }

        return [
            "size"     => $size,
            "width"    => $width,
            "group"    => $group,
            "multiple" => $multiple,
        ];
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if($value['VALUE'] > 0){
            $region = RegionsTable::getList(
                [
                    'filter' => ['ID' => $value['VALUE']],
                    'select' => ['NAME'],
                    'limit' => 1
                ]
            )->fetch();
            return $region['NAME'];
        }
    }

    public static function GetSettingsHTML(
        $arProperty,
        $strHTMLControlName,
        &$arPropertyFields
    ) {
        $settings = self::PrepareSettings($arProperty);

        $arPropertyFields = [
            "HIDE" => ["ROW_COUNT", "COL_COUNT", "MULTIPLE_CNT"],
        ];

        return '
		<tr valign="top">
			<td>'.Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_SETTING_SIZE").':</td>
			<td><input type="text" size="5" name="'.$strHTMLControlName["NAME"]
            .'[size]" value="'.$settings["size"].'"></td>
		</tr>
		<tr valign="top">
			<td>'.Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_SETTING_WIDTH").':</td>
			<td><input type="text" size="5" name="'.$strHTMLControlName["NAME"]
            .'[width]" value="'.$settings["width"].'">px</td>
		</tr>
		<tr valign="top">
			<td>'
            .Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_SETTING_SECTION_GROUP").':</td>
			<td><input type="checkbox" name="'.$strHTMLControlName["NAME"]
            .'[group]" value="Y" '.($settings["group"] == "Y" ? 'checked' : '').'></td>
		</tr>
		<tr valign="top">
			<td>'.Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_SETTING_MULTIPLE").':</td>
			<td><input type="checkbox" name="'.$strHTMLControlName["NAME"]
            .'[multiple]" value="Y" '.($settings["multiple"] == "Y" ? 'checked'
                : '').'></td>
		</tr>
		';
    }


    public static function GetPropertyFieldHtml(
        $arProperty,
        $value,
        $strHTMLControlName
    ) {
        $settings = self::PrepareSettings($arProperty);
        if ($settings["size"] > 1) {
            $size = ' size="'.$settings["size"].'"';
        } else {
            $size = '';
        }

        if ($settings["width"] > 0) {
            $width = ' style="width:'.$settings["width"].'px"';
        } else {
            $width = '';
        }

        $bWasSelect = false;
        $options = self::GetOptionsHtml($arProperty, [$value["VALUE"]],
            $bWasSelect);

        $html = '<select name="'.$strHTMLControlName["VALUE"].'"'.$size.$width
            .'>';
        if ($arProperty["IS_REQUIRED"] != "Y") {
            $html .= '<option value=""'.(!$bWasSelect ? ' selected' : '').'>'
                .Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_NO_VALUE")
                .'</option>';
        }
        $html .= $options;
        $html .= '</select>';

        return $html;
    }

    public static function GetPropertyFieldHtmlMulty(
        $arProperty,
        $value,
        $strHTMLControlName
    ) {
        $max_n = 0;
        $values = [];
        if (is_array($value)) {
            foreach ($value as $property_value_id => $arValue) {
                if (is_array($arValue)) {
                    $values[$property_value_id] = $arValue["VALUE"];
                } else {
                    $values[$property_value_id] = $arValue;
                }

                if (preg_match("/^n(\\d+)$/", $property_value_id, $match)) {
                    if ($match[1] > $max_n) {
                        $max_n = intval($match[1]);
                    }
                }
            }
        }

        $settings = self::PrepareSettings($arProperty);
        if ($settings["size"] > 1) {
            $size = ' size="'.$settings["size"].'"';
        } else {
            $size = '';
        }

        if ($settings["width"] > 0) {
            $width = ' style="width:'.$settings["width"].'px"';
        } else {
            $width = '';
        }

        if ($settings["multiple"] == "Y") {
            $bWasSelect = false;
            $options = self::GetOptionsHtml($arProperty, $values, $bWasSelect);

            $html = '<input type="hidden" name="'.$strHTMLControlName["VALUE"]
                .'[]" value="">';
            $html .= '<select multiple name="'.$strHTMLControlName["VALUE"]
                .'[]"'.$size.$width.'>';
            if ($arProperty["IS_REQUIRED"] != "Y") {
                $html .= '<option value=""'.(!$bWasSelect ? ' selected' : '')
                    .'>'.Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_NO_VALUE")
                    .'</option>';
            }
            $html .= $options;
            $html .= '</select>';
        } else {
            if (end($values) != "" || mb_substr(key($values), 0, 1, LANG_CHARSET) != "n") {
                $values["n".($max_n + 1)] = "";
            }

            $name = $strHTMLControlName["VALUE"]."VALUE";

            $html
                = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'
                .md5($name).'">';
            foreach ($values as $property_value_id => $value) {
                $html .= '<tr><td>';

                $bWasSelect = false;
                $options = self::GetOptionsHtml($arProperty, [$value],
                    $bWasSelect);

                $html .= '<select name="'.$strHTMLControlName["VALUE"].'['
                    .$property_value_id.'][VALUE]"'.$size.$width.'>';
                $html .= '<option value=""'.(!$bWasSelect ? ' selected' : '')
                    .'>'.Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_NO_VALUE")
                    .'</option>';
                $html .= $options;
                $html .= '</select>';

                $html .= '</td></tr>';
            }
            $html .= '</table>';

            $html .= '<input type="button" value="'
                .Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_ADD")
                .'" onClick="if(window.addNewRow){addNewRow(\'tb'.md5($name)
                .'\', -1)}else{addNewTableRow(\'tb'.md5($name)
                .'\', 1, /\[(n)([0-9]*)\]/g, 2)}">';
        }

        return $html;
    }

    public static function GetUIFilterProperty($property,$config,&$field){
        $field['type'] = 'list';
        $field['items'] = [];
        $regions = self::GetRegions();
        if($regions)
        {
            foreach($regions as $region){
                $field['items'][$region['ID']] = $region['NAME'];
            }
        }
    }

    public static function GetPublicViewHTML(
        $arProperty,
        $arValue,
        $strHTMLControlName
    ) {
        static $cache = [];

        $strResult = '';
        $arValue['VALUE'] = intval($arValue['VALUE']);
        if (0 < $arValue['VALUE']) {
            if (!isset($cache[$arValue['VALUE']])) {
                $arFilter = [];
                $intIBlockID = intval($arProperty['LINK_IBLOCK_ID']);
                if (0 < $intIBlockID) {
                    $arFilter['IBLOCK_ID'] = $intIBlockID;
                }
                $arFilter['ID'] = $arValue['VALUE'];
                $arFilter["ACTIVE"] = "Y";
                $arFilter["ACTIVE_DATE"] = "Y";
                $arFilter["CHECK_PERMISSIONS"] = "Y";
                $rsElements = \CIBlockElement::GetList([], $arFilter,
                    false, false,
                    ["ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL"]);
                $cache[$arValue['VALUE']] = $rsElements->GetNext(true, false);
            }
            if (is_array($cache[$arValue['VALUE']])) {
                if (isset($strHTMLControlName['MODE'])
                    && 'CSV_EXPORT' == $strHTMLControlName['MODE']
                ) {
                    $strResult = $cache[$arValue['VALUE']]['ID'];
                } elseif (isset($strHTMLControlName['MODE'])
                    && ('SIMPLE_TEXT' == $strHTMLControlName['MODE']
                        || 'ELEMENT_TEMPLATE' == $strHTMLControlName['MODE'])
                ) {
                    $strResult = $cache[$arValue['VALUE']]["NAME"];
                } else {
                    $strResult = '<a href="'
                        .$cache[$arValue['VALUE']]["DETAIL_PAGE_URL"].'">'
                        .$cache[$arValue['VALUE']]["NAME"].'</a>';;
                }
            }
        }

        return $strResult;
    }

    public static function GetOptionsHtml($arProperty, $values, &$bWasSelect)
    {
        $options = "";
        $bWasSelect = false;

        $regions = self::GetRegions();
        foreach ($regions as $i => $region) {
            $options .= '<option value="'.$region["ID"].'"';
            if (in_array($region["ID"], $values)) {
                $options .= ' selected';
                $bWasSelect = true;
            }
            $options .= '>'.$region["NAME"].'</option>';
        }

        return $options;
    }

    public static function GetExtendedValue($arProperty, $value)
    {
        $html = self::GetPublicViewHTML($arProperty, $value,
            ['MODE' => 'SIMPLE_TEXT']);
        if (mb_strlen($html)) {
            $text = htmlspecialcharsback($html);

            return [
                'VALUE'     => $text,
                'UF_XML_ID' => $text,
            ];
        }

        return false;
    }

    public static function GetRegions()
    {
        $return = [];
        $rs = RegionsTable::getList(['order' => ['SORT' => 'asc']]);
        while ($region = $rs->fetch()) {
            $return[] = $region;
        }

        return $return;
    }
}
?>