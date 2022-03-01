<?php

namespace Kit\Regions\Location\User;

use Bitrix\Main\Service;

/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 07-Feb-18
 * Time: 2:29 PM
 */
class GeoIp
{
    protected $ip;

    public function __construct(
    ) {
        $this->ip = Service\GeoIp\Manager::getRealIp();
    }

    public function getUserCity(
    ) {
        global $DB;
        $return = '';
        if (function_exists('geoip_record_by_name') && $this->ip) {
            $result = geoip_record_by_name($this->ip);
            if ($result['city']) {
                $city = $DB->ForSql(mb_strtolower($result['city']));
                $record = \Bitrix\Main\Application::getConnection()
                    ->query("select `RU` FROM `kit_regions_city` WHERE `EN` = '$city' LIMIT 1;")
                    ->fetch();
                if ($record['RU']) {
                    $return = $record['RU'];
                }
            }
        }

        return $return;
    }
}