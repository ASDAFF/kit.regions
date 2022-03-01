<?php

namespace Sotbit\Regions\System;

use Sotbit\Regions\DTO;
use Sotbit\Regions\Config\Option;
use Bitrix\Main\Loader;
abstract class LocationAbstarctHeandler
{
    const SHOW_QUESTION = 'SHOW_QUESTION';
    const SHOW_REGION_NAME = 'SHOW_REGION_NAME';
    const CONFIRM_DOMAIN = 'CONFIRM_DOMAIN';
    const SHOW_SELECT_REGIONS = 'SHOW_SELECT_REGIONS';
    const REDIRECT_TO_SUBDOMAIN = 'REDIRECT_TO_SUBDOMAIN';

    abstract public function guessRegion(): DTO\ResponseData;
    abstract public function confirmRegion(): DTO\ResponseData;
    abstract public function countryCitys(): DTO\ResponseData;

    /** @return DTO\PreparedCookies[] */
    abstract public function getPreparedCookies(): array;

    public static function getHeandler(int $regionId, int $locationId, DTO\CookieNames $cookieNames): self
    {
        $heandlers = [
            0 => ['heandler' => OneDomenWithoutLocations::class, 'param' => $regionId],
            1 => ['heandler' => MultiDomenWithoutLocations::class, 'param' => $regionId],
            2 => ['heandler' => OneDomenWithLocations::class, 'param' => $locationId],
            3 => ['heandler' => MultiDomenWithLocations::class, 'param' => $locationId],
        ];

        $withLocation = (Option::get('MODE_LOCATION', SITE_ID) == 'Y') ? 1 : 0;
        $singleDomain = (Option::get('SINGLE_DOMAIN', SITE_ID) == 'Y') ? 0 : 1;

        if (!Loader::includeModule('sale')) {
            $withLocation = 0;
        }

        $handlerNumber = 1 * $singleDomain + 2 * $withLocation;

        $heandler = $heandlers[$handlerNumber]['heandler'];
        $param = $heandlers[$handlerNumber]['param'];


        return new $heandler($param, $cookieNames);
    }
}
