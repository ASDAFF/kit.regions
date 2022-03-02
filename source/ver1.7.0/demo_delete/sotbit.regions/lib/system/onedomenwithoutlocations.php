<?php

namespace Sotbit\Regions\System;

use Sotbit\Regions\Internals;
use Sotbit\Regions\DTO;
use Sotbit\Regions\Location;
use Bitrix\Main\Web;
use Sotbit\Regions\System\Traits;

class OneDomenWithoutLocations extends LocationAbstarctHeandler
{
    use Traits\getAllRegionTrait;

    /** @var int $regionId */
    private $regionId;

    /** @var DTO\CookieNames */
    private $cookieNames;

    /** @var DTO\PreparedCookies[] */
    private $cookies;

    public function __construct(int $regionId, DTO\CookieNames $cookieNames)
    {
        $this->regionId = $regionId;
        $this->cookieNames = $cookieNames;
    }

    public function guessRegion(): DTO\ResponseData
    {
        $result = new DTO\ResponseData();

        if ($this->regionId === 0) {

            $userLocation = new Location\User();
            $userGeoData = $userLocation->getUserGeoData();

            $region = $userLocation->getBestMatch($userGeoData);

            $result->currentRegionName = $region['NAME'];
            $result->currentRegionId = $region['ID'];
            $result->actions = [self::SHOW_QUESTION, self::SHOW_REGION_NAME];

            $this->prepareCookies($result);

            return $result;
        }

        $regionName = Internals\RegionsTable::query()
            ->addSelect('ID')
            ->addSelect('NAME')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->where('ID', $this->regionId)
            ->fetch();

        $result->currentRegionName = $regionName['NAME'];
        $result->currentRegionId = $regionName['ID'];
        $result->actions = [self::SHOW_REGION_NAME];

        $this->prepareCookies($result);

        return $result;
    }

    public function confirmRegion(): DTO\ResponseData
    {
        $region = Internals\RegionsTable::query()
            ->addSelect('ID')
            ->addSelect('NAME')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->where('ID', $this->regionId)
            ->fetch();

        $defoultRegion = Internals\RegionsTable::query()
            ->addSelect('CODE')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->where('DEFAULT_DOMAIN', 'Y')
            ->fetch();

        $result = new DTO\ResponseData();
        $result->actions = [self::REDIRECT_TO_SUBDOMAIN];
        $result->currentRegionCode = (new Web\Uri($defoultRegion['CODE']))->getHost();
        $result->currentRegionName = $region['NAME'];
        $result->currentRegionId = $region['ID'];

        $this->prepareCookies($result);

        return $result;
    }

    public function countryCitys(int $countryId=0): DTO\ResponseData
    {
        return $this->getAllRegion([self::SHOW_SELECT_REGIONS]);
    }

    public function getPreparedCookies(): array
    {
        return $this->cookies;
    }

    private function prepareCookies(DTO\ResponseData $response): void
    {
        $this->cookies = [
            new DTO\PreparedCookies($this->cookieNames->region, $response->currentRegionId),
        ];
    }

}