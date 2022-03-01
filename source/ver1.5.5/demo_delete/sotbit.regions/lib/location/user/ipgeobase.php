<?php

namespace Sotbit\Regions\Location\User;

use Bitrix\Main\Service;

/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 07-Feb-18
 * Time: 2:29 PM
 */
class IpGeoBase
{
    protected $ip;

    public function __construct(
    ) {
        $this->ip = Service\GeoIp\Manager::getRealIp();
    }

    public function getUserCity(
    ) {
        $return = '';
        if ($this->ip) {
            $result = file_get_contents("http://ipgeobase.ru:7020/geo?ip=" . $this->ip);
            if ($result && self::isXML($result)) {
                $xml = new \SimpleXMLElement($result);
                $return = (string)$xml->ip->city;
            }
        }
        return $return;
    }

    private function isXML(
        $str
    ) {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $options = (mb_strpos($str,
                '<!DOCTYPE') !== false) ? (LIBXML_DTDLOAD + LIBXML_DTDVALID) : 0;
        simplexml_load_string($str,
            'SimpleXMLElement',
            $options);
        $errors = libxml_get_errors();
        return (empty($errors) or $errors[0]->level == LIBXML_ERR_WARNING) ? true : false;
    }
}