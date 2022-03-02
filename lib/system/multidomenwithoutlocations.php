<?php

namespace Kit\Regions\System;

use Kit\Regions\Internals\RegionsTable;
use Kit\Regions\DTO;
use Kit\Regions\Location;
use Kit\Regions\System\Traits;
use Bitrix\Main\Web;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Context;
use Exception;

class MultiDomenWithoutLocations extends LocationAbstarctHeandler
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

        $regions = $this->getResultByDomenAndLicationId();
        $region = $this->getRegionByDomen($regions);

        if ((int)$region['ID'] === $this->regionId) {
            $result->actions = [self::SHOW_REGION_NAME];
        } else {
            $result->actions = [self::SHOW_QUESTION, self::SHOW_REGION_NAME];
        }

        $result->currentRegionName = $region['NAME'];
        $result->currentRegionId = $region['ID'];

        $this->prepareCookies($result);

        return $result;
    }

    public function confirmRegion(): DTO\ResponseData
    {
        $region = RegionsTable::query()
            ->addSelect('ID')
            ->addSelect('NAME')
            ->addSelect('CODE')
            ->where('ID', $this->regionId)
            ->fetch();

        $result = new DTO\ResponseData();
        $result->actions = [self::REDIRECT_TO_SUBDOMAIN];
        $result->currentRegionCode = (new Web\Uri($region['CODE']))->getHost();
        $result->currentRegionName = $region['NAME'];
        $result->currentRegionId = $region['ID'];

        $this->prepareCookies($result);

        return $result;
    }

    public function countryCitys(): DTO\ResponseData
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

    private function getResultByDomenAndLicationId(): array
    {
        $domen = $this->getDomen();

        $filter = Query\Query::filter()
            ->logic('or')
            ->where('ID', $this->regionId)
            ->whereLike('CODE', "%$domen%");

        $regions = RegionsTable::query()
            ->addSelect('ID')
            ->addSelect('NAME')
            ->addSelect('CODE')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->where($filter)
            ->fetchAll();

        return $regions;
    }

    private function getRegionByDomen(array $regions): array
    {
        $domen = $this->getDomen();

        $regionByDomen = array_reduce($regions, function(array $curry, array $i) use ($domen) {
            return is_int(stripos($i['CODE'], $domen)) ? $i : $curry;
        }, []);

        if ($regionByDomen === []) {
            throw new Exception('TODO : ' . __METHOD__);
        }

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
}