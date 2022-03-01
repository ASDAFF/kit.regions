<?php

use Kit\Regions\Maps\Base;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// create coordinates
$arResult['MAP_DATA'] = [];
if (!empty($arResult['REGIONS'])) {
    foreach ($arResult['REGIONS'] as $regionId => $regionVal) {
        $position = [];

        // points info
        $text = '';
        $arFieldLang = Base::getUserFields();
        foreach ($arParams['MARKER_FIELDS'] as $field) {
            $lang = '';
            if (!empty($regionVal[$field])) {
                if (!empty($arFieldLang[$field]) && $field != "NAME") {
                    $lang = $arFieldLang[$field];
                }

                $text .= PHP_EOL.($lang ? $lang.": " : '');
                if (Base::is_serialized($regionVal[$field])) {
                    $text .= implode(PHP_EOL, unserialize($regionVal[$field]));
                } else {
                    $text .= $regionVal[$field];
                }
            }
        }
        $text = trim($text);

        // yandex
        if ($arParams['TYPE'] == "yandex") {
            if (!empty($regionVal['MAP_YANDEX'][0]['VALUE'])) {
                $coordinate = explode(",", $regionVal['MAP_YANDEX'][0]['VALUE']);

                if (empty($arResult['MAP_DATA'])) {
                    $arResult['MAP_DATA'] = [
                        'yandex_lat'   => ($arParams['MAP_CALCULATE_CENTER'] == "Y" ?
                            $arResult['MAP_CENTER'][0] : $coordinate[0]),
                        'yandex_lon'   => ($arParams['MAP_CALCULATE_CENTER'] == "Y" ?
                            $arResult['MAP_CENTER'][1] : $coordinate[1]),
                        'yandex_scale' => $arParams['MAP_SCALE'],
                    ];
                }

                $marker = ($regionVal['MAP_YANDEX']['MARKER'] ? $regionVal['MAP_YANDEX']['MARKER'] : ($arResult['MARKER'] ? $arResult['MARKER'] : ''));

                $position[] = [
                    'MARKER' => $marker,
                    'TEXT' => $text,
                    'LAT'  => $coordinate[0],
                    'LON'  => $coordinate[1],
                ];
            }
        }

        // google
        if ($arParams['TYPE'] == "google") {
            if (!empty($regionVal['MAP_GOOGLE']['VALUE'][0]['VALUE'])) {
                $coordinate = explode(",", $regionVal['MAP_GOOGLE']['VALUE'][0]['VALUE']);

                if (empty($arResult['MAP_DATA'])) {
                    $arResult['MAP_DATA'] = [
                        'google_lat'   => ($arParams['MAP_CALCULATE_CENTER'] == "Y" ?
                            $arResult['MAP_CENTER'][0] : $coordinate[0]),
                        'google_lon'   => ($arParams['MAP_CALCULATE_CENTER'] == "Y" ?
                            $arResult['MAP_CENTER'][1] : $coordinate[1]),
                        'google_scale' => $arParams['MAP_SCALE'],
                    ];
                }

                $marker = ($regionVal['MAP_GOOGLE']['MARKER'] ? $regionVal['MAP_GOOGLE']['MARKER'] : ($arResult['MARKER'] ? $arResult['MARKER'] : ''));

                $position[] = [
                    'MARKER' => $marker,
                    'TEXT' => $text,
                    'LAT'  => $coordinate[0],
                    'LON'  => $coordinate[1],
                ];
            }
        }

        if (!empty($position)) {
            $arResult['MAP_DATA']['PLACEMARKS'] =
                array_merge(($arResult['MAP_DATA']['PLACEMARKS'] ?? []), $position);
        }
    }
}