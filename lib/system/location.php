<?php

namespace Kit\Regions\System;

use Bitrix\Main\Loader;
use Kit\Regions\Internals\LocationsTable;
use Kit\Regions\Internals\RegionsTable;
use Kit\Regions\Location\User;
use Kit\Regions\Location\Domain;
use Kit\Regions\Helper\LocationType;
use Bitrix\Main\Text\Encoding;

/**
 * Class Location
 *
 * @package Kit\Regions\System
 */
class Location
{
    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function findRegion()
    {
        $return = [];
        $region = $this->getExistRegion();
        if ($region['ID'] > 0) {
            $return = $region;
        } else {
            $userLocation = new User();
            $userCity = $userLocation->getUserCity();
            $location = $this->getByName($userCity);
            if ($location['ID'] > 0)
            {
                $region = $this->findRegionByIdLocation($location['ID']);

                if ($region['ID'] > 0)
                {
                    $region['LOCATION'] = $location;
                    $return = $region;
                }
                else{
                    $return['LOCATION'] = $location;
                }
            }
        }

        return $return;
    }

    /**
     * @param string $name
     *
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getByName($name = '')
    {
        $return = [];
        \Bitrix\Main\Loader::includeModule('sale');
        $location = \Bitrix\Sale\Location\LocationTable::getList([
            'filter' => [
                '=NAME.NAME'        => Encoding::convertEncodingToCurrent($name),
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID
            ],
            'select' => [
                '*',
                'NAME.*',
            ],
            'cache'  => [
                'ttl' => 36000000,
            ],
        ])->fetch();
        if ($location['ID'] > 0) {
            $return = $location;
        }

        return $return;
    }

    /**
     * @param int $idLocation
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function findChain($idLocation = 0)
    {
        $return = [];
        if ($idLocation > 0)
        {
            $return['CITY'] = $idLocation;
//            $rs = \Bitrix\Sale\Location\LocationTable::getList([
//                'filter' => [
//                    '=ID'               => $idLocation,
//                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
//                ],
//                'select' => [
//                    'PARENT.REGION_ID',
//                    'PARENT.COUNTRY_ID',
//                    'NAME_RU'      => 'PARENT.NAME.NAME',
//                    'TYPE_CODE'    => 'PARENT.TYPE.CODE',
//                    'TYPE_NAME_RU' => 'PARENT.TYPE.NAME.NAME',
//                ],
//            ]);
            $rs = \Bitrix\Sale\Location\LocationTable::getList([
                'filter' => [
                    '=ID'               => $idLocation,
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID
                ],
                'select' => [
                    '*'
                ],
            ]);
            if ($item = $rs->fetch())
            {
                $return['REGION'] = $item['REGION_ID'];
                $return['COUNTRY'] = $item['COUNTRY_ID'];
                $return['PARENT'] = $item['PARENT_ID'];
            }
        }

        return $return;
    }

    /**
     * @param $idLocation
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function findRegionByIdLocation($idLocation)
    {
        $return = [];
        $regionId = 0;
        if ($idLocation > 0)
        {
            $chain = $this->findChain($idLocation);
            $regions = [];
            $rs = LocationsTable::getList([
                'filter' => [
                    'LOCATION_ID'     => $chain,
                    '%REGION.SITE_ID' => SITE_ID,
                ],
                'select' => ['REGION_ID', 'LOCATION_ID'],
                'cache'  => [
                    'ttl' => 36000000,
                ],
            ]);
            while ($loc = $rs->fetch())
            {
                $regions[] = $loc;
            }

            if ($regions)
            {
                foreach ($regions as $region)
                {
                    if ($region['LOCATION_ID'] == $chain['CITY'])
                    {
                        $regionId = $region['REGION_ID'];
                        break;
                    }
                }
                if (!$regionId)
                {
                    foreach ($regions as $region)
                    {
                        if ($region['LOCATION_ID'] == $chain['REGION'])
                        {
                            $regionId = $region['REGION_ID'];
                            break;
                        }
                    }
                }
                if (!$regionId)
                {
                    foreach ($regions as $region)
                    {
                        if ($region['LOCATION_ID'] == $chain['PARENT'])
                        {
                            $regionId = $region['REGION_ID'];
                            break;
                        }
                    }
                }
                if (!$regionId)
                {
                    foreach ($regions as $region)
                    {
                        if ($region['LOCATION_ID'] == $chain['COUNTRY'])
                        {
                            $regionId = $region['REGION_ID'];
                            break;
                        }
                    }
                }
            }
        }

        if ($regionId > 0) {
            $location = RegionsTable::getList(
                [
                    'filter' => [
                        'ID'       => $regionId,
                        '%SITE_ID' => SITE_ID,
                    ],
                    'limit'  => 1,
                    'cache'  => [
                        'ttl' => 36000000,
                    ],
                ]
            )->fetch();
            if ($location['ID'] > 0) {
                $return = $location;
            }
        }

        return $return;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */

    public function getNameByID($id)
    {
        $return = [];

        if($id)
        {
            $location = \Bitrix\Sale\Location\LocationTable::getList([
                'filter' => [
                    'ID' => $id,
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ],
                'select' => [
                    '*',
                    'NAME.*',
                ],
                'limit' => 1,
                'cache'  => [
                    'ttl' => 36000000,
                ],
            ])->fetch();
            if ($location['ID'] > 0)
            {
                $return = $location;
            }
        }

        return $return;
    }

    protected function getExistRegion()
    {
        $return = [];
        if (!\Kit\Regions\Location\Domain::$autoDef && $_COOKIE['kit_regions_id'] > 0)
        {
            $region = RegionsTable::getList(
                [
                    'filter' => [
                        'ID'       => $_COOKIE['kit_regions_id'],
                        '%SITE_ID' => SITE_ID
                    ],
                    'limit'  => 1,
                    'cache'  => [
                        'ttl' => 36000000,
                    ],
                ]
            )->fetch();
            if ($region['ID'] > 0)
            {
                if ($_COOKIE['kit_regions_location_id'] > 0)
                {
                    $location = \Bitrix\Sale\Location\LocationTable::getList([
                        'filter' => [
                            'ID' => $_COOKIE['kit_regions_location_id'],
                            '=NAME.LANGUAGE_ID' => LANGUAGE_ID
                        ],
                        'select' => [
                            '*',
                            'NAME.*',
                        ],
                        'cache'  => [
                            'ttl' => 36000000,
                        ],
                    ])->fetch();
                    if ($location['ID'] > 0)
                    {
                        $region['LOCATION'] = $location;
                    }
                }
            }
            $return = $region;
        }

        return $return;
    }

    public static function getRandLocations($limit = 1)
    {
        Loader::includeModule('sale');

        $return = [];
        $arRegion = [];

        $rsReg = RegionsTable::getList(
            [
                'filter' => [
                    '%SITE_ID' => SITE_ID,
                ],
                'cache'  => [
                    'ttl' => 36000000,
                ],
                'limit' => $limit,
                'select' => [
                    'NAME',
                    'CODE',
                    'ID',
                    'DEFAULT_DOMAIN'
                ],
                'order' => [
                    'SORT' => 'asc'
                ]
            ]
        );

        while($region = $rsReg->Fetch())
        {
            $region['URL'] = $region['CODE'];
            $return[$region['ID']] = $region;

            $rsLoc = LocationsTable::getList([
                'filter' => [
                    'REGION_ID'     => $region["ID"],
                    '%REGION.SITE_ID' => SITE_ID,
                ],
                'select' => ['REGION_ID', 'LOCATION_ID'],
                'cache'  => [
                    'ttl' => 36000000,
                ],
            ]);

            $arLoc = [];

            while ($loc = $rsLoc->fetch())
            {
                $arLoc[] = $loc["LOCATION_ID"];
            }

            if($arLoc)
            {
                $rsLocTable = \Bitrix\Sale\Location\LocationTable::getList(
                    [
                        'filter' => [
                            'TYPE_ID'           => LocationType::getCity(),
                            '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                            '=ID' => $arLoc,
                        ],
                        'order'  => [
                            'SORT'=>'asc',
                            'TYPE_ID' => 'asc',
                            'NAME.NAME' => 'asc'
                        ],
                        'select' => [
                            '*',
                            'CODE',
                            'COUNTRY_ID',
                            'TYPE_ID',
                            'NAME.NAME',
                            'PARENT.COUNTRY_ID',
                        ],
                        'cache'  => [
                            'ttl' => 36000000,
                        ],
                    ]
                );

                while ($location = $rsLocTable->fetch())
                {
                    $return[$region['ID']]['LOCATION'] = $location;
                    $return[$region['ID']]['NAME'] = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                    $return[$region['ID']]['LOC_ID'] = $location['ID'];
                    break;
                }

                if(!$return[$region['ID']]['LOCATION'])
                {
                    $rsLocTableDefault = \Bitrix\Sale\Location\LocationTable::getList(
                        [
                            'filter' => [
                                '=CHILDREN.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                '=CHILDREN.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                '=ID' => $arLoc,
                            ],
                            'order'  => [
                                'SORT'=>'asc',
                                'TYPE_ID' => 'asc',
                                'NAME.NAME' => 'asc'
                            ],
                            'select' => [
                                '*',
                                'CHILDREN.*',
                                'SALE_LOCATION_LOCATION_NAME_NAME' => 'CHILDREN.NAME.NAME',
                                'TYPE_CODE' => 'CHILDREN.TYPE.CODE',
                                'TYPE_NAME_RU' => 'CHILDREN.TYPE.NAME.NAME',
                                //'ID' => 'CHILDREN.ID.*'
                            ],
                            'cache'  => [
                                'ttl' => 36000000,
                            ],
                        ]
                    );

                    while ($location = $rsLocTableDefault->fetch())
                    {
                        if($location['TYPE_CODE'] == 'CITY')
                        {
                            $location["ID"] = $return[$region['ID']]['LOC_ID'] = $location["SALE_LOCATION_LOCATION_CHILDREN_ID"];
                            $return[$region['ID']]["LOCATION"] = $location;
                            $return[$region['ID']]['NAME'] = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                            break;
                        }
                    }

                    if(!$return[$region['ID']]['LOCATION'])
                    {
                        $location = \Bitrix\Sale\Location\LocationTable::getList([
                            'filter' => [
                                'TYPE_ID' => LocationType::getCity(),
                                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                            ],
                            'order' => [
                                'SORT' => 'ASC'
                            ],
                            'select' => [
                                '*',
                                'NAME.*',
                            ],
                            'cache'  => [
                                'ttl' => 36000000,
                            ],
                            'limit' => 1
                        ])->fetch();

                        $return[$region['ID']]['LOCATION'] = $location;
                        $return[$region['ID']]['NAME'] = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                        $return[$region['ID']]['LOC_ID'] = $location['ID'];
                    }
                }
            }
        }

        return $return;
    }


    public static function getAllLocations()
    {
        Loader::includeModule('sale');

        $arLocations = [];

        $rs = \Bitrix\Sale\Location\LocationTable::getList(
            [
                'filter' => [
                    'TYPE_ID'           => LocationType::getCity(),
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ],
                'order'  => [
                    'SORT'=>'asc',
                    'TYPE_ID' => 'asc',
                    'NAME.NAME' => 'asc'
                ],
                'select' => [
                    'ID',
                    'NAME.NAME',
                ],
                'cache'  => [
                    'ttl' => 36000000,
                ],
            ]
        );

        $key = 0;

        while ($location = $rs->fetch())
        {
            $arLocations[$key]["ID"] = $location["ID"];
            $arLocations[$key]["NAME"] = $location["SALE_LOCATION_LOCATION_NAME_NAME"];
            $key++;
        }

        return $arLocations;

    }

    public static function getLocations()
    {
        Loader::includeModule('sale');
        $return = [];
        $domain = new Domain();
        $favoriteCodes = self::getFavorites();
        $currentId = $domain->getProp('LOCATION')['ID'];
        $return['TITLE_CITIES'] = [];
        $return['REGION_LIST'] = [];
        $return['FAVORITES'] = [];
        $countries = [];

        $rs = \Bitrix\Sale\Location\LocationTable::getList(
            [
                'filter' => [
                    'TYPE_ID'           => [1, LocationType::getCity()],
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ],
                'order'  => [
                    'SORT'=>'asc',
                    'TYPE_ID' => 'asc',
                    'NAME.NAME' => 'asc'
                ],
                'select' => [
                    'ID',
                    'CODE',
                    'COUNTRY_ID',
                    'TYPE_ID',
                    'NAME.NAME',
                    'PARENT.COUNTRY_ID',
                ],
                'cache'  => [
                    'ttl' => 36000000,
                ],
            ]
        );
        while ($location = $rs->fetch()) {
            if($currentId > 0)
            {
                if ($location['ID'] == $currentId)
                {
                    $location['CURRENT'] = 'Y';
                    $return['USER_REGION_NAME']
                        = $return['USER_REGION_NAME_LOCATION']
                        = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                    $fullName
                        = \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById(
                        $location['ID'],
                        ['INVERSE' => true]
                    );

                    $pos = mb_strpos($fullName, ',');
                    $return['USER_REGION_FULL_NAME'] = '<span data-entity="item2" data-index="'
                        .$location['ID'].'">'.mb_substr($fullName, 0, $pos + 1, LANG_CHARSET)
                        .'</span>'.mb_substr($fullName, $pos + 1, mb_strlen($fullName), LANG_CHARSET);
                }
            }
            else{
                $name = $domain->getProp('NAME');
                if($location['SALE_LOCATION_LOCATION_NAME_NAME'] == $name){
                    $location['CURRENT'] = 'Y';
                    $return['USER_REGION_NAME']
                        = $return['USER_REGION_NAME_LOCATION']
                        = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                    $fullName
                        = \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById(
                        $location['ID'],
                        ['INVERSE' => true]
                    );

                    $pos = mb_strpos($fullName, ',');
                    $return['USER_REGION_FULL_NAME'] = '<span data-location-id="'
                        .$location['ID'].'">'.mb_substr($fullName, 0, $pos + 1, LANG_CHARSET)
                        .'</span>'.mb_substr($fullName, $pos + 1, mb_strlen($fullName), LANG_CHARSET);
                }
            }

            if ($location['TYPE_ID'] == 1) {
                $countries[$location['ID']] = $location;
                $countries[$location['ID']]['CITY'] = [];
            } else {
                if (isset($countries[$location['COUNTRY_ID']]['CITY'])) {
                    if (in_array($location['CODE'], $favoriteCodes)) {
                        $return['FAVORITES'][$location['COUNTRY_ID']][]
                            = $location;
                    }
                    if (
                        $location['SALE_LOCATION_LOCATION_NAME_NAME']
                        == \Bitrix\Main\Localization\Loc::getMessage('KIT_REGIONS_MOSCOW')
                        || $location['SALE_LOCATION_LOCATION_NAME_NAME']
                        == \Bitrix\Main\Localization\Loc::getMessage('KIT_REGIONS_SP')

                    ) {
                        $return['TITLE_CITIES'][] = $location;
                    }
                    $letter = mb_substr(
                        $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                        0,
                        1,
                        LANG_CHARSET
                    );
                    $countries[$location['COUNTRY_ID']]['CITY'][$letter][]
                        = $location;

                    if (!in_array($location['CODE'], $favoriteCodes)) {
                        $return['REGION_LIST'][] = [
                            'ID'   => $location['ID'],
                            'NAME' => $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                        ];
                        if($location['CURRENT'] == 'Y'){
                            $return['USER_REGION_NAME'] = $return['USER_REGION_NAME_LOCATION'] = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                            $return['USER_REGION_ID'] = $location['ID'];
                        }
                    }
                }
            }
        }
        if ($return['REGION_LIST'] && $return['FAVORITES']) {
            $favorites = [];
            foreach($return['FAVORITES'] as $country){
                foreach ($country as $favorite){
                    $favorites[] = [
                        'ID' => $favorite['ID'],
                        'NAME' => $favorite['SALE_LOCATION_LOCATION_NAME_NAME'],
                    ];
                }
            }
            $return['REGION_LIST'] = array_merge($favorites,$return['REGION_LIST']);
        }
        $return['REGION_LIST_COUNTRIES'] = $countries;
        return $return;
    }
    public function getFavorites(){
        $return = [];
        $rs = \Bitrix\Sale\Location\DefaultSiteTable::getList(
            [
                'select' => ['LOCATION_CODE'],
                'filter' => ['SITE_ID' => SITE_ID]
            ]
        );
        while ($location = $rs->fetch()) {
            $return[$location['LOCATION_CODE']] = $location['LOCATION_CODE'];
        }

        return $return;
    }
}
