<?php

namespace Sotbit\Regions\System;

use Sotbit\Regions\Internals\RegionsTable;
use Sotbit\Regions\Location\User;

/**
 * Class Single
 *
 * @package Sotbit\Regions\System
 * @author  Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class Single
{
    /**
     * @param array $location
     *
     * @return array
     */
    public function findRegion($location = [])
    {
        $return = [];
        $region = $this->getExistRegion();

        if ($region['ID'] > 0) {
            $return = $region;
        }else{
            $userLocation = new User();
            $userCity = $userLocation->getUserCity();
            $region = $this->getByName($userCity);
            $return = $region;
        }

        return $return;
    }

    protected function getByName($userCity)
    {
        $return = [];

        $region = RegionsTable::getList(
            [
                'filter' => [
                    'NAME'     => $userCity,
                    '%SITE_ID' => SITE_ID,
                ],
                'limit'  => 1,
                'cache'  => [
                    'ttl' => 36000000,
                ],
            ]
        )->fetch();

        if ($region['ID'] > 0) {
            $return = $region;
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
                        '%SITE_ID' => SITE_ID,
                    ],
                    'limit'  => 1,
                    'cache'  => [
                        'ttl' => 36000000,
                    ],
                ]
            )->fetch();

            $return = $region;
        }

        return $return;
    }
}