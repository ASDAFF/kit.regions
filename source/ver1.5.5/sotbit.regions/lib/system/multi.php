<?php

namespace Sotbit\Regions\System;

use Bitrix\Main\Application;
use Sotbit\Regions\Internals\RegionsTable;

class Multi
{
    public function findRegion($location = [])
    {
        $return = [];
        $region = [];

        $region = $this->getByDomain();

        if($region['ID'])
        {
            $region = $this->getExistRegion($region);
            $userLocation = new \Sotbit\Regions\Location\User();
            $userCity = $userLocation->getUserCity();
            if(!$this->withLocation)
                $region['REAL_REGION'] = $this->getByName($userCity);
            elseif(!$region['LOCATION'])
            {
                $loc = new Location();
                $region['LOCATION'] = $loc->getByName($userCity);
                $region['REAL_REGION'] = $loc->findRegionByIdLocation($region['LOCATION']['ID']);
            }


        }

        $return = $region;

        return $return;
    }

    protected function getByName($userCity)
    {
        $return = [];
        if(!$_COOKIE['sotbit_regions_id'])
        {
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
        }

        return $return;
    }

    protected function getByDomain()
    {
        $return = [];
        $region = [];

        $context = Application::getInstance()->getContext();
        $server = $context->getServer();
        $httpHost = $server->getHttpHost();
        $arHttpHost = explode(':', $httpHost);
        $currentDomain = $arHttpHost[0];
        $currentDomain = str_replace('www.','',$currentDomain);

        $region = RegionsTable::getList(
            [
                'filter' => [
                    'CODE'     => [
                        'http://'.$currentDomain,
                        'https://'.$currentDomain,
                        'http://www.'.$currentDomain,
                        'https://www.'.$currentDomain,
                    ],
                    '%SITE_ID' => SITE_ID,
                ],
                'limit'  => 1,
                'cache'  => [
                    'ttl' => 36000000,
                ],
            ]
        )->fetch();

        if (!$region['ID'])
        {
            $domains = [];
            $rs = RegionsTable::getList(
                [
                    'order'  => ['SORT' => 'asc'],
                    'filter' => ['%SITE_ID' => SITE_ID],
                    'cache'  => [
                        'ttl' => 36000000,
                    ],
                ]
            );
            while ($domain = $rs->fetch())
            {
                $domains[] = $domain;
                $url = str_replace([
                    'http://',
                    'https://',
                ], '', $domain['CODE']);
                if (mb_strpos($url, '.') === 0) {
                    $return = $domain;
                    break;
                }
            }
            if (!$return['ID']) {
                if ($domains[0]) {
                    $return = $domains[0];
                }
            }
        } else {
            $return = $region;
        }

        return $return;
    }

    protected function getExistRegion($region)
    {
        $return = $region;

        if(!\Sotbit\Regions\Location\Domain::$autoDef)
        {
            if(isset($_COOKIE['sotbit_regions_id']) && $_COOKIE['sotbit_regions_id'] != $region['ID'])
            {
                unset($_COOKIE['sotbit_regions_id']);
                unset($_COOKIE['sotbit_regions_city_choosed']);
                if(isset($_COOKIE['sotbit_regions_location_id']))
                    unset($_COOKIE['sotbit_regions_location_id']);
                //setcookie('sotbit_regions_id', "", time()-3600, '/');
                //setcookie('sotbit_regions_city_choosed', "", time()-3600, '/');
            }

            if($_COOKIE['sotbit_regions_location_id'])
            {
                \Bitrix\Main\Loader::includeModule('sale');
                $location = \Bitrix\Sale\Location\LocationTable::getList([
                    'filter' => [
                        'ID' => $_COOKIE['sotbit_regions_location_id'],
                        '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
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
                    $return['LOCATION'] = $location;
                }
            }
        }

        return $return;
    }
}