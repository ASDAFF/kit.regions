<?php

namespace Kit\Regions\System;

use Kit\Regions\Internals\RegionsTable;
use Kit\Regions\Location\User;

/**
 * Class Single
 *
 * @package Kit\Regions\System
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
            $userGeoData = $userLocation->getUserGeoData();
            $region = $this->getByName($userGeoData->cityName);
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
        if (!\Kit\Regions\Location\Domain::$autoDef && $_COOKIE['kit_regions_id'] > 0)
        {
            $region = RegionsTable::getList(
                [
                    'filter' => [
                        'ID'       => $_COOKIE['kit_regions_id'],
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