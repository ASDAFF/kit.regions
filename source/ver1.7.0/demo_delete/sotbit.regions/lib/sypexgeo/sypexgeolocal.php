<?php

namespace Sotbit\Regions\SypexGeo;

use Bitrix\Main\Service\GeoIp;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;;
use SxGeo;

require __DIR__ . '/SxGeo.php';


class SypexGeoLocal extends GeoIp\Base
{

    public function getTitle()
    {
        return Loc::getMessage('SOTBIT_REGION_SYPEX_GEO_HEANDLER_TITLE');
    }

    /**
     * @return string Handler description.
     */
    public function getDescription()
    {
        return Loc::getMessage('SOTBIT_REGION_SYPEX_GEO_HEANDLER_DESCRIPTION');
    }

    /**
     * @param string $ip Ip address
     * @param string $lang Language identifier
     * @return Result | null
     */
    public function getDataResult($ip, $lang = '')
    {
        $dataResult = new GeoIp\Result;
		$geoData = new GeoIp\Data();

		$geoData->ip = $ip;
		$geoData->lang = $lang = $lang <> '' ? $lang : 'ru';

        try {

            $db = new SxGeo(__DIR__ . '/SxGeoCity.dat');
            $data = $db->getCityFull($ip);
			$geoData->countryName = $data['country']['name_'.$lang];
			$geoData->countryCode = $data['country']['iso'];
			$geoData->regionName = $data['region']['name_'.$lang];
			$geoData->regionCode = $data['region']['iso'];
			$geoData->cityName = $data['city']['name_'.$lang];
			$geoData->latitude = $data['city']['lat'];
			$geoData->longitude = $data['city']['lon'];
			$geoData->timezone = $data['region']['timezone'];
            $dataResult->setGeoData($geoData);

        } catch (\Exception $e) {
            $dataResult->addError(new Error($e->getMessage()));
        }

        return $dataResult;
    }

    public function getSupportedLanguages()
	{
		return ['en', 'ru'];
	}
}