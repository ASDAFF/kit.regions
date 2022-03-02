<?php

use \Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Sotbit\Regions\Internals\RegionsTable;
use \Sotbit\Regions\Internals\FieldsTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Class SotbitRegionsChooseComponent
 *
 * @author Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class SotbitRegionsDataComponent extends \CBitrixComponent
{
    /**
     * @param $arParams
     *
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        if(!isset($arParams['REGION_FIELDS']) || count($arParams['REGION_FIELDS']) == 0){
            $arParams['REGION_FIELDS'] = ['ID'];
        }
        if(!isset($arParams['CACHE_TIME'])){
            $arParams['CACHE_TIME'] = 36000000;
        }
        return $arParams;
    }

    public function executeComponent()
    {
        global $APPLICATION;
        if (!Loader::includeModule('sotbit.regions')
            || \SotbitRegions::isDemoEnd()
        ) {
            return false;
        }

        if (isset($_COOKIE["sotbit_regions_id"])) {
            $this->arParams['REGION_ID'] = $_COOKIE["sotbit_regions_id"];
        } else {
            $id = RegionsTable::query()
                ->addSelect('ID')
                ->where('SITE_ID', serialize([SITE_ID]))
                ->where('DEFAULT_DOMAIN', 'Y')
                ->fetch()['ID']
            ;
            $this->arParams['REGION_ID'] = $id;
        }

        if(!$this->arParams['REGION_ID']){
            return false;
        }

        $cache = Cache::createInstance();
        if ($cache->initCache($this->arParams['CACHE_TIME'], "sotbit_regions_data_tags")) {
            $tags = $cache->getVars();
        }
        elseif ($cache->startDataCache()) {
            $tags = \SotbitRegions::getTags(array(SITE_ID));
            $tags[] = ['CODE' => 'ID','NAME' => 'Id'];
            $cache->endDataCache($tags);
        }

        $fields = [];
        // array user fields
        $arUserType = \SotbitRegions::getUserTypeFields();

        // main fields
        $rs = RegionsTable::getList(
            [
                'filter' => [
                    'ID' => $this->arParams['REGION_ID']
                ],
                'cache'  => [
                    'ttl' => $this->arParams['CACHE_TIME'],
                ],
            ]
        );
        while($region = $rs->fetch()){
            foreach($region as $key => $value){
                if(in_array($key,$this->arParams['REGION_FIELDS'])){
                    if(!is_array($value)) {
                        $unserizlize = unserialize($value);
                        if (is_array($unserizlize)) {
                            $value = $unserizlize;
                        }
                    }
                    $fields[$key] = $value;
                }
            }
        }

        // user fields
        if(count($this->arParams['REGION_FIELDS']) != count($fields)){
            $rs = FieldsTable::getList(
                [
                    'filter' => [
                        'ID_REGION' => $this->arParams['REGION_ID'],
                        'CODE' => $this->arParams['REGION_FIELDS'],
                    ],
                    'select' => ['CODE','VALUE'],
                    'cache'  => [
                        'ttl' => $this->arParams['CACHE_TIME'],
                    ],
                ]
            );
            while($field = $rs->fetch()){
                if(!empty($arUserType[$field['CODE']]) && $arUserType[$field['CODE']]['USER_TYPE_ID'] == "file") {
                    if(!empty($field['VALUE'])) {
                        if($arUserType[$field['CODE']]['MULTIPLE'] == "Y") {
                            $unserialize = unserialize($field['VALUE']);
                            if (is_array($unserialize)) {
                                $field['VALUE'] = [];
                                foreach ($unserialize as $v) {
                                    $file = \CFile::GetFileArray($v);
                                    $field['VALUE'][] = $file;
                                }
                            }
                        } else {
                            $file = \CFile::GetFileArray($field['VALUE']);
                            $field['VALUE'] = $file;
                        }
                    }
                } else {
                    $unserizlize = unserialize($field['VALUE']);
                    if (is_array($unserizlize)) {
                        $field['VALUE'] = $unserizlize;
                    }
                }
                $fields[$field['CODE']] = $field['VALUE'];

            }
        }
        $this->arResult['FIELDS'] = [];

        foreach($fields as $key => $field){
            $this->arResult['FIELDS'][$key] = [
                'VALUE' => $field,
                'CODE'  => $key
            ];

            foreach($tags as $tag){
                if($tag['CODE'] == $key){
                    $this->arResult['FIELDS'][$key]['NAME'] = $tag['NAME'];
                    break;
                }
            }
        }
        if($this->arResult['FIELDS']['MAP_YANDEX']){
            $ll = explode(',', $this->arResult['FIELDS']['MAP_YANDEX']['VALUE'][0]['VALUE']);
            $data = [
                'yandex_lat'   => $ll[0],
                'yandex_lon'   => $ll[1],
                'yandex_scale' => 10,
                'PLACEMARKS'   => [
                    0 => [
                        'LON'  => $ll[1],
                        'LAT'  => $ll[0],
                        'TEXT' => '',
                    ],
                ],
            ];
            ob_start();
            $APPLICATION->IncludeComponent(
                "bitrix:map.yandex.view",
                "",
                [
                    "CONTROLS"      => [
                        "ZOOM",
                        "MINIMAP",
                        "TYPECONTROL",
                        "SCALELINE",
                    ],
                    "INIT_MAP_TYPE" => "MAP",
                    "MAP_DATA"      => serialize($data),
                    "MAP_HEIGHT"    => "500",
                    "MAP_ID"        => "2",
                    'API_KEY'       => $this->arResult['FIELDS']['MAP_YANDEX']['API_KEY'],
                    "MAP_WIDTH"     => "100%",
                    "OPTIONS"       => [
                        "ENABLE_SCROLL_ZOOM",
                        "ENABLE_DBLCLICK_ZOOM",
                        "ENABLE_DRAGGING",
                    ],
                ]
            );
            $this->arResult['FIELDS']['MAP_YANDEX']['VALUE'] = ob_get_contents();
            ob_end_clean();
        }
        if($this->arResult['FIELDS']['MAP_GOOGLE']){
            $ll = explode(',', $this->arResult['FIELDS']['MAP_GOOGLE']['VALUE'][0]['VALUE']);
            $data = [
                'google_lat'   => $ll[0],
                'google_lon'   => $ll[1],
                'google_scale' => 10,
                'PLACEMARKS'   => [
                    0 => [
                        'LON'  => $ll[1],
                        'LAT'  => $ll[0],
                        'TEXT' => '',
                    ],
                ],
            ];
            ob_start();
            $APPLICATION->IncludeComponent(
                "bitrix:map.google.view",
                "",
                [
                    "API_KEY"       => $this->arResult['FIELDS']['MAP_GOOGLE']['API_KEY'],
                    "CONTROLS"      => [
                        "SMALL_ZOOM_CONTROL",
                        "TYPECONTROL",
                        "SCALELINE",
                    ],
                    "INIT_MAP_TYPE" => "ROADMAP",
                    "MAP_DATA"      => serialize($data),
                    "MAP_HEIGHT"    => "500",
                    "MAP_ID"        => "1",
                    "MAP_WIDTH"     => "100%",
                    "OPTIONS"       => [
                        "ENABLE_SCROLL_ZOOM",
                        "ENABLE_DBLCLICK_ZOOM",
                        "ENABLE_DRAGGING",
                        "ENABLE_KEYBOARD",
                    ],
                ]
            );
            $this->arResult['FIELDS']['MAP_GOOGLE']['VALUE'] = ob_get_contents();
            ob_end_clean();
        }
        $this->includeComponentTemplate();
    }
}

?>