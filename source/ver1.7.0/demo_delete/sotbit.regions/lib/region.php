<?php

namespace Sotbit\Regions;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Sotbit\Regions\Config\Option;
use Sotbit\Regions\Internals\RegionsTable;
use Sotbit\Regions\System\Location;
use Sotbit\Regions\System\Multi;
use Sotbit\Regions\System\Single;
use Sotbit\Regions\Internals\LocationsTable;
use Sotbit\Regions\Helper\LocationType;

class Region
{
    /**
     * @var Location|Multi|Single
     */
    public $system;
    public $withLocation = true;
    public $singleDomain = false;

    /**
     * Region constructor.
     *
     * @throws LoaderException
     */
    public function __construct()
    {
        $this->withLocation = (Option::get('MODE_LOCATION', SITE_ID) == 'Y') ? true : false;
        $this->singleDomain = (Option::get('SINGLE_DOMAIN', SITE_ID) == 'Y') ? true : false;
        $this->defineSystem();
    }

    /**
     * @return array
     * @throws LoaderException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function findRegion()
    {
        $this->system->withLocation = $this->withLocation;
        $return = $this->system->findRegion();

        $location = $return['LOCATION'];

        if ($this->withLocation && !$return['ID'])
        {
            $this->defineSystem(false);
            $return = $this->system->findRegion($location);
        }

        if (!$return)
        {
            $return = $this->setSomeRegion();
        }

        if($this->withLocation && $return['ID'] && !$location)
        {
            $return = $this->setSomeLocation($return);
        }

        if ($location['ID'] > 0) {
            $return['LOCATION'] = $location;
        }

        return $return;
    }

    /**
     * @param bool $withLocation
     *
     * @throws LoaderException
     */
    protected function defineSystem($withLoc = true)
    {
        if ($this->singleDomain && $withLoc && $this->withLocation && Loader::includeModule('sale'))
        {
            $this->system = new System\Location();
        } else {
            if ($this->singleDomain)
            {
                $this->system = new System\Single();
            } else {
                $this->system = new System\Multi();
            }
        }
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function setSomeLocation($region)
    {
        $return = $region;
        if($return["ID"])
        {
            $rs = LocationsTable::getList([
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

            while ($loc = $rs->fetch())
            {
                $arLoc[] = $loc["LOCATION_ID"];
            }

            if($arLoc)
            {
                $rs = \Bitrix\Sale\Location\LocationTable::getList(
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

                while ($location = $rs->fetch())
                {
                    $return['LOCATION'] = $location;
                    break;
                }

                if(!$return['LOCATION'])
                {
                    $rs = \Bitrix\Sale\Location\LocationTable::getList(
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

                    while ($location = $rs->fetch())
                    {
                        if($location['TYPE_CODE'] == 'CITY')
                        {
                            $location["ID"] = $location["SALE_LOCATION_LOCATION_CHILDREN_ID"];
                            $return["LOCATION"] = $location;
                            break;
                        }
                    }

                    if(!$return['LOCATION'])
                    {
                        $return['LOCATION'] = \Bitrix\Sale\Location\LocationTable::getList([
                            'filter' => [
                                'TYPE_ID' => LocationType::getCity()
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
                    }
                }
            }
        }

        return $return;
    }

    public function getAllRegions()
    {
        $return = [];
        $rs = RegionsTable::getList(
            [
                'filter' => [
                    '%SITE_ID' => SITE_ID,
                ],
                'cache' => [
                    'ttl' => 36000000,
                ],
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
    }

    public function getRandRegions($limit = 1)
    {
        $return = [];
        $rs = RegionsTable::getList(
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

        while($region = $rs->Fetch())
        {
            $region['URL'] = $region['CODE'];
            $region['LOC_ID'] = '';
            $return[] = $region;
        }

        return $return;
    }

    public function setSomeRegion()
    {
        $return = [];
        $location = RegionsTable::getList(
            [
                'filter' => [
                    '%SITE_ID' => SITE_ID,
                ],
                'limit'  => 1,
                'cache'  => [
                    'ttl' => 36000000,
                ],
				'order' => [
                    'DEFAULT_DOMAIN' => 'desc',
                    'ID'
                ]
            ]
        )->fetch();
        if ($location['ID'] > 0) {
            $return = $location;
        }

        return $return;
    }
}