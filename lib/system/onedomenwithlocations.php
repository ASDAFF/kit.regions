<?php

namespace Kit\Regions\System;

use Kit\Regions\DTO;
use Bitrix\Main\Web;
use Kit\Regions\System\Traits;
use Kit\Regions\Location;
use Kit\Regions\Internals;

class OneDomenWithLocations extends LocationAbstarctHeandler
{

    use Traits\getAllLocationTrait;

    /** @var int $locationId */
    private $locationId;

    /** @var DTO\PreparedCookies[] */
    private $cookies = [];

    /** @var DTO\CookieNames */
    private $cookieNames;

    public function __construct(int $locationId, DTO\CookieNames $cookieNames)
    {
        $this->locationId = $locationId;
        $this->cookieNames = $cookieNames;
    }

    public function guessRegion(): DTO\ResponseData
    {
        $result = new DTO\ResponseData();
        $userLocation = new Location\User();
        $result->actions = [self::SHOW_REGION_NAME];

        if ($this->locationId === 0) {
            $geoData = $userLocation->getUserGeoData();
            $location = $userLocation->findByGeodata($geoData);
            $location = $userLocation->getBestMatchWithLocation($location);

            $result->actions[] = self::SHOW_QUESTION;
            $result->currentRegionName = $location['NAME'];
            $result->currentRegionId = $location['ID'];

            return $result;
        }

        $location = $userLocation->getLocationById($this->locationId);

        if (count($location) === 0) {
            $result->actions[] = self::SHOW_QUESTION;
        }

        $location = $userLocation->getBestMatchWithLocation($location);

        $result->currentRegionName = $location['NAME'];
        $result->currentRegionId = $location['ID'];
        $result->kitRegionsId = $location['REGION_ID'];

        $this->prepareCookies($result);

        return $result;
    }

    public function confirmRegion(): DTO\ResponseData
    {
        $result = new DTO\ResponseData();
        $userLocation = new Location\User();

        $regionCode = Internals\RegionsTable::query()
            ->addSelect('CODE')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->where('DEFAULT_DOMAIN', 'Y')
            ->fetch();

        $result->actions = [self::REDIRECT_TO_SUBDOMAIN];
        $result->currentRegionCode = (new Web\Uri($regionCode['CODE']))->getHost();

        $location = $userLocation->getLocationById($this->locationId);
        $location = $userLocation->getBestMatchWithLocation($location);

        $result->currentRegionName = $location['NAME'];
        $result->currentRegionId = $location['ID'];
        $result->kitRegionsId = $location['REGION_ID'];

        $this->prepareCookies($result);

        return $result;
    }

    public function countryCitys(int $countryId=0): DTO\ResponseData
    {
        return $this->getAllLocation([self::SHOW_SELECT_REGIONS], $countryId);
    }

    public function getPreparedCookies(): array
    {
        return $this->cookies;
    }

    private function prepareCookies(DTO\ResponseData $response): void
    {
        $this->cookies = [
            new DTO\PreparedCookies($this->cookieNames->location, $response->currentRegionId),
            new DTO\PreparedCookies($this->cookieNames->region, $response->kitRegionsId),
        ];
    }

}