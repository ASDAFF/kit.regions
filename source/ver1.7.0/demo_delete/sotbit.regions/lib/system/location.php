<?php

namespace Sotbit\Regions\System;

use Bitrix\Main\Loader;
use Sotbit\Regions\Internals\LocationsTable;
use Sotbit\Regions\Internals\RegionsTable;
use Sotbit\Regions\Location\User;
use Sotbit\Regions\Location\Domain;
use Sotbit\Regions\Helper\LocationType;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Service\GeoIp;
use Bitrix\Sale\Location as SaleLocation;

/**
 * Class Location
 *
 * @package Sotbit\Regions\System
 * @author  Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class Location
{

    public function __construct()
    {
        Loader::includeModule('sale');
    }

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
            $userGeoData = $userLocation->getUserGeoData();
            $location = $this->findByGeodata($userGeoData);
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

            $rs = SaleLocation\LocationTable::getList([
                'filter' => [
                    '=ID'               => $idLocation,
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
            $location = SaleLocation\LocationTable::getList([
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
        if (!\Sotbit\Regions\Location\Domain::$autoDef && $_COOKIE['sotbit_regions_id'] > 0)
        {
            $region = RegionsTable::getList(
                [
                    'filter' => [
                        'ID'       => $_COOKIE['sotbit_regions_id'],
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
                if ($_COOKIE['sotbit_regions_location_id'] > 0)
                {
                    $location = SaleLocation\LocationTable::getList([
                        'filter' => [
                            'ID' => $_COOKIE['sotbit_regions_location_id'],
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
                $rsLocTable = SaleLocation\LocationTable::getList(
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
                    $rsLocTableDefault = SaleLocation\LocationTable::getList(
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
                        $location = SaleLocation\LocationTable::getList([
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

        $rs = SaleLocation\LocationTable::getList(
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

        $rs = SaleLocation\LocationTable::getList(
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
                        == \Bitrix\Main\Localization\Loc::getMessage('SOTBIT_REGIONS_MOSCOW')
                        || $location['SALE_LOCATION_LOCATION_NAME_NAME']
                        == \Bitrix\Main\Localization\Loc::getMessage('SOTBIT_REGIONS_SP')

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

    public static function getFavorites(){
        $return = [];
        $rs = SaleLocation\DefaultSiteTable::getList(
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

    public function findByGeodata(GeoIp\Data $data): array
    {
        $type = array_column(SaleLocation\TypeTable::query()
            ->setSelect(['ID', 'CODE'])
            ->fetchAll(), 'ID', 'CODE',
        );

        $contryType = (int)$type['COUNTRY'] ?? 0;
        $regionType = (int)$type['REGION'] ?? 0;
        $cityType = (int)$type['CITY'] ?? 0;

        $initialRequest = SaleLocation\LocationTable::query()->setSelect(['*', 'NAME.*']);

        if (!empty($data->countryName)) {
            $margins = $this->searchByType(clone $initialRequest, $contryType, $data->countryName);
        }

        if (isset($data->countryName) && count($margins) > 0) {
            $initialRequest
                ->where('LEFT_MARGIN', '>=', $margins['LEFT_MARGIN'])
                ->where('RIGHT_MARGIN', '<=', $margins['RIGHT_MARGIN']);
        }

        $result = isset($data->cityName)
            ? $this->searchByType(clone $initialRequest, $cityType, $data->cityName)
            : [];

        if (count($result) !== 0) {
            return $this->langCorrection($result);
        }

        if (empty($data->regionName)) {
            return [];
        }

        $regionData = $this->searchByType(clone $initialRequest, $regionType, $data->regionName);

        if (count($regionData) === 0) {
            return [];
        }

        if ($contryType !== 0) {
            $initialRequest->where('TYPE_ID', $cityType);
        }

        $result = $initialRequest->where('PARENT_ID', $regionData['ID'])->fetch();

        return $this->langCorrection($result);
    }

    /** @return string[] */
    private function getLevenshtein1(string $word): array
    {
        $words = array();
        for ($i = 0; $i < strlen($word); $i++) {
            // insertions
            $words[] = substr($word, 0, $i) . '_' . substr($word, $i);
            // deletions
            $words[] = substr($word, 0, $i) . substr($word, $i + 1);
            // substitutions
            $words[] = substr($word, 0, $i) . '_' . substr($word, $i + 1);
        }
        // last insertion
        $words[] = $word . '_';
        return $words;
    }

    private function searchByType(Query\Query $initialRequest, int $type, string $name): array
    {
        $currentCharset = Encoding::convertEncodingToCurrent($name);
        $names = $this->getLevenshtein1($currentCharset);

        $filter = array_reduce($names, function (Query\Filter\ConditionTree $curry, $i) {
            return $curry->whereLike('NAME.NAME', $i);
        }, Query\Query::filter()->logic('or'));

        if ($type !== 0) {
            $initialRequest->where('TYPE_ID',  $type);
        }

        $result = $initialRequest
            ->where($filter)
            ->fetchAll();

        if (count($result) === 1) {
            return $result[0];
        }

        if (count($result) > 1) {
            return array_reduce($result, function($curry, $i) use ($currentCharset) {
                $key = 'SALE_LOCATION_LOCATION_NAME_NAME';
                return levenshtein($i[$key], $currentCharset) < levenshtein($curry[$key], $currentCharset) ? $i : $curry;
            }, current($result));
        }

        return [];
    }

    private function langCorrection(array $locationData): array
    {
        if (LANGUAGE_ID === $locationData['SALE_LOCATION_LOCATION_NAME_LANGUAGE_ID']) {
            return $locationData;
        }

        $langs = SaleLocation\Name\LocationTable::query()
            ->addSelect('LANGUAGE_ID')
            ->where('LOCATION_ID', $locationData['ID'])
            ->fetchAll();

        if (!in_array(LANGUAGE_ID, array_column($langs, 'LANGUAGE_ID'))) {
            return $locationData;
        }

        $result = SaleLocation\LocationTable::query()
            ->setSelect(['*', 'NAME.*'])
            ->where('ID', (int)$locationData['ID'])
            ->where('NAME.LANGUAGE_ID', LANGUAGE_ID)
            ->setLimit(1)
            ->fetch();

        return $result;
    }
}
