<?php

use Bitrix\Main\{Application, Loader};
use Sotbit\Regions\Config\Option;
use Sotbit\Regions\Internals\{FieldsTable, RegionsTable};
use Sotbit\Regions\Location\Domain;
use Sotbit\Regions\Maps\Base;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Class SotbitRegionsMapsComponent
 *
 * @author Andrey Sapronov <a.sapronov@sotbit.ru>
 * Date: 04.11.2019
 */
class SotbitRegionsMapsComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        if (Loader::includeModule('sotbit.regions') && !\SotbitRegions::isDemoEnd()) {
            $regions = array_keys($this->getRegions());

            // type map
            if (empty($arParams['TYPE'])) {
                $mapsType = Base::getMapsType();
                $mapsTypeDefault = array_keys($mapsType);
                $arParams['TYPE'] = $mapsTypeDefault[0];
            }

            // regions
            if (empty($arParams['REGIONS'])) {
                $arParams['REGIONS'] = $regions;
            } else {
                $arParams['REGIONS'] = array_intersect($arParams['REGIONS'], $regions);
            }

            // scale
            if (empty($arParams['MAP_SCALE']) || !in_array($arParams['MAP_SCALE'], Base::getScale())) {
                $arParams['MAP_SCALE'] = Base::DEFAULT_SCALE;
            }

            // marker info
            if (empty($arParams['MARKER_FIELDS'])) {
                $arParams['MARKER_FIELDS'] = Base::DEFAULT_USER_FIELDS;
            } else {
                $arResult = Base::getUserFields();
                $arParams['MARKER_FIELDS'] = array_intersect($arParams['MARKER_FIELDS'], array_keys($arResult));
            }

            // center
            if (empty($arParams['MAP_CALCULATE_CENTER'])) {
                $arParams['MAP_CALCULATE_CENTER'] = "Y";
            }

            // api key
            $domain = new Domain();
            if ($arParams['TYPE'] == "yandex") {
                $arParams['API_KEY'] = $domain->getProp('MAP_YANDEX')['API_KEY'];
                if(empty($arParams['API_KEY'])) {
                    $arParams['API_KEY'] = Option::get('MAPS_YANDEX_API', SITE_ID);
                }

            } elseif ($arParams['TYPE'] == "google") {
                $arParams['API_KEY'] = $domain->getProp('MAP_GOOGLE')['API_KEY'];
                if(empty($arParams['API_KEY'])) {
                    $arParams['API_KEY'] = Option::get('MAPS_GOOGLE_API', SITE_ID);
                }
            }


            return $arParams;
        }
    }

    /**
     * @return mixed|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {
        if (Loader::includeModule('sotbit.regions') && !\SotbitRegions::isDemoEnd()) {

            // marker file
            $this->arResult['MARKER'] = '';
            $markerFile = Option::get('MAPS_MARKER', SITE_ID);
            if (!empty($markerFile)) {
                $file = $_SERVER['DOCUMENT_ROOT'].$markerFile;
                if (file_exists($file) && \CFile::IsImage($file)) {
                    $this->arResult['MARKER'] = $markerFile;
                }
            }

            // merge regions and fields
            $regions = $this->getRegions($this->arParams['REGIONS']);
            $fields = FieldsTable::getList(
                [
                    'select' => ['ID_REGION', 'CODE', 'VALUE'],
                    'filter' => [
                        'ID_REGION' => $this->arParams['REGIONS'],
                    ],
                    'cache'  => [
                        'ttl' => 36000000,
                    ],
                ]
            )->fetchAll();
            foreach ($fields as $field) {
                if (!empty($regions[$field['ID_REGION']])) {
                    $regions[$field['ID_REGION']][$field['CODE']] = $field['VALUE'];
                }
            }

            // calculate the center of the map
            if ($this->arParams['MAP_CALCULATE_CENTER'] == "Y" && extension_loaded('bcmath')) {
                $mapCenter = [];
                foreach ($regions as $value) {
                    $coordinate = false;
                    switch ($this->arParams['TYPE']) {
                        case 'yandex':
                            if ($value['MAP_YANDEX'][0]['VALUE']) {
                                $coordinate = explode(",", $value['MAP_YANDEX'][0]['VALUE']);
                            }
                            break;

                        case 'google':
                            if ($value['MAP_GOOGLE']['VALUE'][0]['VALUE']) {
                                $coordinate = explode(",", $value['MAP_GOOGLE']['VALUE'][0]['VALUE']);
                            }
                            break;

                        default:
                            break;
                    }
                    if (!empty($coordinate)) {
                        $mapCenter['LAT'][] = $coordinate[0];
                        $mapCenter['LON'][] = $coordinate[1];
                    }
                }

                $mapCenterCalculateLat = 0;
                $mapCenterCalculateLon = 0;
                if (!empty($mapCenter['LAT']) && !empty($mapCenter['LON'])) {
                    bcscale(14);
                    $mapCenterCalculateLat = bcadd(max($mapCenter['LAT']), min($mapCenter['LAT']));
                    $mapCenterCalculateLon = bcadd(max($mapCenter['LON']), min($mapCenter['LON']));

                    $mapCenterCalculateLat = bcdiv($mapCenterCalculateLat, 2);
                    $mapCenterCalculateLon = bcdiv($mapCenterCalculateLon, 2);
                }

                $this->arResult['MAP_CENTER'] = [$mapCenterCalculateLat, $mapCenterCalculateLon];
            }

            $this->arResult['REGIONS'] = $regions;

            $this->includeComponentTemplate();
        }
    }

    public function getRegions($filterRegions = false)
    {
        $regions = [];

        $filter['%SITE_ID'] = SITE_ID;
        if (!empty($filterRegions)) {
            $filter['ID'] = $filterRegions;
        }

        $rs = RegionsTable::getList(
            [
                'select' => [
                    'ID',
                    'CODE',
                    'NAME',
                    'MAP_YANDEX',
                    'MAP_GOOGLE',
                ],
                'filter' => $filter,
                'cache'  => [
                    'ttl' => 36000000,
                ],
            ]
        );

        while ($region = $rs->fetch()) {
            $regions[$region['ID']] = $region;
        }


        return $regions;
    }
}

?>