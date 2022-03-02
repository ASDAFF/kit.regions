<?php

namespace Kit\Regions\System;

use Kit\Regions\Internals;
use Kit\Regions\DTO;
use Bitrix\Sale\Location as SaleLocation;
use Kit\Regions\Location;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Web;
use Bitrix\Main\Context;
use Kit\Regions\System\Traits;

class MultiDomenWithLocations extends LocationAbstarctHeandler
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

        $regions = $this->getResultByDomenAndRegionId($location['REGION_ID']);

        $regionByDomen = $this->getRegionIdByDomen($regions);

        if ((int)current($regionByDomen)['ID'] !== (int)$location['REGION_ID']) {
            $newLocation = $this->getBestMatchByRegionName($regionByDomen);

            $result->currentRegionName = $$newLocation['NAME'];
            $result->currentRegionId = $$newLocation['LOCATION_ID'];
            $result->actions[] = self::SHOW_QUESTION;

            return $result;
        }

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

        $result->actions = [self::REDIRECT_TO_SUBDOMAIN];

        $location = $userLocation->getLocationById($this->locationId);
        $location = $userLocation->getBestMatchWithLocation($location);

        $result->currentRegionName = $location['NAME'];
        $result->currentRegionId = $location['ID'];
        $result->kitRegionsId = $location['REGION_ID'];

        $regionCode = Internals\RegionsTable::query()
            ->addSelect('CODE')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->where('ID', $location['REGION_ID'])
            ->fetch();

        $result->currentRegionCode = (new Web\Uri($regionCode['CODE']))->getHost();

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

    private function getResultByDomenAndRegionId(int $regionId): array
    {
        $domen = $this->getDomen();

        $filter = Query\Query::filter()
            ->logic('or')
            ->where('ID', $regionId)
            ->whereLike('CODE', "%$domen%");

        $regions = Internals\RegionsTable::query()
            ->addSelect('ID')
            ->addSelect('NAME')
            ->addSelect('CODE')
            ->addSelect('REGIONS.LOCATION_ID', 'LOCATION_ID')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->where($filter)
            ->fetchAll();

        return $regions;
    }

    protected function getRegionIdByDomen(array $regions): array
    {
        $domen = $this->getDomen();

        $regionByDomen = array_filter($regions, function(array $i) use ($domen) {
            return is_int(stripos($i['CODE'], $domen));
        });

        return $regionByDomen;
    }

    private function getDomen(): string
    {
        $context = Context::getCurrent();
        $domen = $context->getServer()->getHttpHost();
        $port = $context->getServer()->getServerPort();
        $domenWithoutProt = mb_ereg_replace(":{$port}", '', $domen);
        $protocol = $context->getRequest()->isHttps() ? 'https://' : 'http://';

        return "{$protocol}$domenWithoutProt";
    }

    private function getBestMatchByRegionName(array $regionByDomen): array
    {
        $locationNames = SaleLocation\Name\LocationTable::query()
            ->addSelect('NAME')
            ->addSelect('LOCATION_ID')
            ->where('LANGUAGE_ID', LANGUAGE_ID)
            ->whereIn('LOCATION_ID', array_column($regionByDomen, 'LOCATION_ID'))
            ->fetchAll();

        $regionName = current($regionByDomen)['NAME'];

        $bastMatch = array_reduce($locationNames, function(array $curry, array $i) use($regionName) {
            return levenshtein($regionName, $i['NAME']) < levenshtein($regionName, $curry['NAME'])
                ? $i : $curry;
        }, current($locationNames));

        return $bastMatch;
    }
}