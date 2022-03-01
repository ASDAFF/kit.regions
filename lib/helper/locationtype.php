<?php

namespace Kit\Regions\Helper;

use Bitrix\Main\Loader;
use Bitrix\Sale\Location\TypeTable;
use Kit\Regions\Config\Option;

/**
 * Class LocationType
 *
 * @package Kit\Regions\Helper
 */
class LocationType
{
    const DEFAULT_CITY_ID = 5;
    /**
     * Get list of location type
     *
     * @return array
     */
    public static function getList()
    {
        $types = [];

        if(Loader::includeModule('sale')) {
            $res = TypeTable::getList([
                'select' => ['*', 'NAME_RU' => 'NAME.NAME'],
                'filter' => ['=NAME.LANGUAGE_ID' => LANGUAGE_ID],
                'cache' => ['ttl' => 36000000],
            ]);
            while ($item = $res->fetch()) {
                $types[$item['ID']] = $item;
            }
        }

        return $types;
    }

    /**
     * Get list of location type
     *
     * @return array
     */
    public static function getListFormat()
    {
        $typesFormat = [];

        $locationTypeList = self::getList();
        if(!empty($locationTypeList)) {
            $typesFormat = array_map(function ($v) {
                return $v['NAME_RU']." (".$v['CODE'].")";
            }, $locationTypeList);
        }

        return $typesFormat;
    }

    /**
     * Get id CITY
     *
     * @return int
     */
    public static function getCity()
    {
        $city = Option::get('LOCATION_TYPE', SITE_ID);

        return ($city ? $city : self::DEFAULT_CITY_ID);
    }

}