<?php

namespace Sotbit\Regions\Location\User;

use Bitrix\Main\Service;

/**
 * Class GeoIpManager
 *
 * @package Sotbit\Regions\Location\User
 * @author  Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class GeoIpManager
{
    /**
     * @var false|string
     */
    protected $ip;

    /**
     * GeoIpManager constructor.
     */
    public function __construct(
    ) {
        $this->ip = Service\GeoIp\Manager::getRealIp();
    }

    /**
     * @return string
     */
    public function getUserCity(
    ) {
        $return = '';
        if ($this->ip) {
            $GeoData = Service\GeoIp\Manager::getDataResult($this->ip,
                'ru',
                array(
                    'countryName',
                    'cityName'
                ));
            if (!is_null($GeoData)) {
                $return = $GeoData->getGeoData()->cityName;
            }
        }
        return $return;
    }
}