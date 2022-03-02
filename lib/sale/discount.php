<?php

namespace Kit\Regions\Sale;

use Bitrix\Main\{EventResult, Loader, Context, Localization\Loc};
use Kit\Regions\Internals\RegionsTable;
use Kit\Regions\Location\Domain;

/**
 * Class Discount
 *
 * @package Kit\Regions\Sale
 * Date: 19.11.2019
 */
class Discount extends \CSaleCondCtrlComplex
{
    public static function GetControlDescr()
    {
        $description = parent::GetControlDescr();
        $description['EXECUTE_MODULE'] = 'all';
        $description['SORT'] = 200;

        return $description;
    }

    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'KitRegion';
    }

    public static function GetControlShow($arParams)
    {
        $arControls = static::GetControls();
        $arResult = [
            'controlgroup' => true,
            'group'        => false,
            'label'        => Loc::getMessage(\KitRegions::moduleId.'_GROUP_TITLE'),
            'showIn'       => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children'     => [],
        ];
        foreach ($arControls as $arOneControl) {
            $arResult['children'][] = [
                'controlId' => $arOneControl['ID'],
                'group'     => false,
                'label'     => $arOneControl['LABEL'],
                'showIn'    => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control'   => [
                    [
                        'id'   => 'prefix',
                        'type' => 'prefix',
                        'text' => $arOneControl['PREFIX'],
                    ],
                    static::GetLogicAtom($arOneControl['LOGIC']),
                    static::GetValueAtom($arOneControl['JS_VALUE']),
                ],
            ];
        }

        return $arResult;
    }

    public static function GetControls($strControlID = false)
    {
        $arControlList = [
            self::GetControlID() => [
                'ID'         => self::GetControlID(),
                'FIELD'      => 'KIT_REGION',
                'FIELD_TYPE' => 'string',
                'LABEL'      => Loc::getMessage(\KitRegions::moduleId.'_FIELD_REGION_TITLE'),
                'PREFIX'     => Loc::getMessage(\KitRegions::moduleId.'_FIELD_REGION_TITLE'),
                'LOGIC'      => static::GetLogic([BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ]),
                'JS_VALUE'   => [
                    'type'       => 'select',
                    'multiple'   => 'Y',
                    'show_value' => 'Y',
                    'values'     => self::getRegions(),
                ],
                'PHP_VALUE'  => '',
            ],
        ];
        if ($strControlID === false) {
            return $arControlList;
        } elseif (isset($arControlList[$strControlID])) {
            return $arControlList[$strControlID];
        } else {
            return false;
        }
    }

    public static function generate($oneCondition, $params, $control, $subs = false)
    {
        $result = '';
        if (is_string($control))
        {
            $control = static::getControls($control);
        }
        $error = !is_array($control);

        $values = array();
        if (!$error)
        {
            $values = static::check($oneCondition, $oneCondition, $control, false);
            $error = ($values === false);
        }

        if (!$error)
        {
            $stringArray = 'array('.implode(',', $values['value']).')';
            $type = $oneCondition['logic'];
            $result = "(class_exists(".static::getClassName()."::class) ? ".static::getClassName()."::checkRegion($stringArray, '{$type}') : true)";
        }

        return $result;
    }

    public static function checkRegion(array $region, $type)
    {
        $regions = self::getRegions();
        if(!empty($regions)) {
            $regions = array_keys($regions);
        }

        $domain = new Domain();
        $currentId = $domain->getProp('ID');

        if(empty($currentId) || !in_array($currentId, $regions))
        {
            return false;
        }

        $currentId = (int)$currentId;
        if ($type === 'Equal')
        {
            return in_array($currentId, $region);
        }
        elseif($type === 'Not')
        {
            return !in_array($currentId, $region);
        }

        return false;
    }

    public function getRegions()
    {
        $regions = [];
        if (Loader::includeModule('kit.regions')) {
            $rs = RegionsTable::getList(['select' => ['ID', 'NAME']]);
            while ($region = $rs->fetch()) {
                $regions[$region['ID']] = $region['NAME'];
            }
        }

        return $regions;
    }
}