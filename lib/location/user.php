<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 07-Feb-18
 * Time: 2:20 PM
 */

namespace Kit\Regions\Location;

use Kit\Regions\Config\Option;

class User
{
    protected $findUserMethod = 'ipgeobase';
    protected $ip;

    public function __construct(
    ) {
        if (Option::get('FIND_USER_METHOD',
            SITE_ID)) {
            $this->findUserMethod = Option::get('FIND_USER_METHOD',
                SITE_ID);
        }
    }

    public function getUserCity(
    ) {
        $city = '';

        if ($this->findUserMethod == 'geoip') {
            $GeoIp = new User\GeoIp();
            $city = $GeoIp->getUserCity();
        } elseif ($this->findUserMethod == 'statistic') {
            $Statistic = new User\Statistic();
            $city = $Statistic->getUserCity();
        } elseif ($this->findUserMethod == 'ipgeobase') {
            $IpGeoBase = new User\IpGeoBase();
            $city = $IpGeoBase->getUserCity();
        }
        if (!$city) {
            $IpGeoManager = new User\GeoIpManager();
            $city = $IpGeoManager->getUserCity();
        }

        return $city;
    }
}