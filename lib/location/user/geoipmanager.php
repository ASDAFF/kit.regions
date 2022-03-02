<?php

namespace Kit\Regions\Location\User;

use Bitrix\Main\Service\GeoIp;

/**
 * Class GeoIpManager
 *
 * @package Kit\Regions\Location\User
 */
class GeoIpManager
{
    /** @var false|string */
    protected $ip;

    public function __construct()
    {
        $this->ip = GeoIp\Manager::getRealIp();
    }

    public function getUserCity(): GeoIp\Data
    {
        $lang = 'ru';
        $geoData = GeoIp\Manager::getDataResult($this->ip, $lang, [
            'countryName',
            'regionName',
            'cityName'
        ]);
        if (!is_null($geoData)) {
            return $geoData->getGeoData();
        }
        return new GeoIp\Data();
    }
}