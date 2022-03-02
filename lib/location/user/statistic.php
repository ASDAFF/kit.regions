<?php

namespace Kit\Regions\Location\User;

use Bitrix\Main\Loader;
use Bitrix\Main\Service\GeoIp;

/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 07-Feb-18
 * Time: 2:29 PM
 */
class Statistic
{
    public function getUserGeoData(): GeoIp\Data
	{
        if (!Loader::includeModule('statistic')) {
            return false;
        }
        $cityObj = new \CCity();
        $arThisCity = $cityObj->GetFullInfo();
		$data = new GeoIp\Data();
		$data->countryName = $arThisCity['COUNTRY_NAME']['VALUE'];
		$data->regionName = $arThisCity['REGION_NAME']['VALUE'];
		$data->cityName = $arThisCity['CITY_NAME']['VALUE'];
        return $data;
    }
}

/*		return array(
			"IP_ADDR" => array(
				"TITLE" => GetMessage("STAT_CITY_IP_ADDR"),
				"VALUE~" => $this->ip_addr,
				"VALUE" => htmlspecialcharsbx($this->ip_addr),
			),
			"COUNTRY_CODE" => array(
				"TITLE" => GetMessage("STAT_CITY_COUNTRY_CODE"),
				"VALUE~" => $this->country_code,
				"VALUE" => htmlspecialcharsbx($this->country_code),
			),
			"COUNTRY_NAME" => array(
				"TITLE" => GetMessage("STAT_CITY_COUNTRY_NAME"),
				"VALUE~" => $this->country_full_name,
				"VALUE" => htmlspecialcharsbx($this->country_full_name),
			),
			"REGION_NAME" => array(
				"TITLE" => GetMessage("STAT_CITY_REGION_NAME"),
				"VALUE~" => $this->region_name,
				"VALUE" => htmlspecialcharsbx($this->region_name),
			),
			"CITY_NAME" => array(
				"TITLE" => GetMessage("STAT_CITY_CITY_NAME"),
				"VALUE~" => $this->city_name,
				"VALUE" => htmlspecialcharsbx($this->city_name),
			),
		);*/