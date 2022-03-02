<?php

namespace Kit\Regions\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Context;
use Bitrix\Main\Web;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Engine\ActionFilter;
use Kit\Regions\DTO;
use Kit\Regions\System\LocationAbstarctHeandler;

class ChooseComponentController extends Controller
{

    // const COOKIE_SHOW_REQGION_QUESTION = 'kit_regions_city_choosed';
    const COOKIE_REGION_ID = 'kit_regions_id';
    const COOKIE_REGION_LOCATOON_ID = 'kit_regions_location_id';

    public function configureActions()
    {
        return [
            'getRegion' => ['-prefilters' => [ActionFilter\Authentication::class]],
            'setRegion' => ['-prefilters' => [ActionFilter\Authentication::class]],
            'showLocations' => ['-prefilters' => [ActionFilter\Authentication::class]],
         ];

    }

    public function getRegionAction(int $redirectRegionId=0, int $deletCookies=0): DTO\ResponseData
    {
        $regionId = !$deletCookies ? (int)$this->getRequest()->getCookieRaw(static::COOKIE_REGION_ID) : 0;
        $locationId = !$deletCookies ? (int)$this->getRequest()->getCookieRaw(static::COOKIE_REGION_LOCATOON_ID) : 0;

        if ($redirectRegionId > 0 && $redirectRegionId !== $regionId && !(bool)$deletCookies) {
            $regionId = $redirectRegionId;
            $locationId = $redirectRegionId;
        }

        $cookieNames = new DTO\CookieNames(self::COOKIE_REGION_ID, self::COOKIE_REGION_LOCATOON_ID);
        $heandler = LocationAbstarctHeandler::getHeandler($regionId, $locationId, $cookieNames);
        $response = $heandler->guessRegion();

        if ($redirectRegionId > 0) {
            $this->setCookies($heandler->getPreparedCookies());
        }

        return $response;
    }

    public function setRegionAction(int $regionId): DTO\ResponseData
    {
        $cookieNames = new DTO\CookieNames(self::COOKIE_REGION_ID, self::COOKIE_REGION_LOCATOON_ID);
        $heandler = LocationAbstarctHeandler::getHeandler($regionId, $regionId, $cookieNames);
        $response = $heandler->confirmRegion();

        $this->setCookies($heandler->getPreparedCookies(), $response->currentRegionCode);

        return $response;
    }

    public function showLocationsAction(int $countryId=0): DTO\ResponseData
    {
        $cookieNames = new DTO\CookieNames(self::COOKIE_REGION_ID, self::COOKIE_REGION_LOCATOON_ID);
        $heandler = LocationAbstarctHeandler::getHeandler(0, 0, $cookieNames);
        $response = $heandler->countryCitys($countryId);

        return $response;
    }

    public static function urlGenerate(string $action, array $queryParams): Web\Uri
    {
        $controller = "kit:regions.ChooseComponentController.{$action}";
        $queryParams['sessid'] = bitrix_sessid();
        return UrlManager::getInstance()->create($controller, $queryParams);
    }

    /** @param DTO\PreparedCookies[] $params */
    private function setCookies(array $params, ?string $domen=null): void
    {
        $context = Context::getCurrent();
        $isHttps = $context->getRequest()->isHttps();
        $setDomen = $domen ?? $context->getServer()->getHttpHost();

        /** @var Web\Cookie[] */
        $cookies = array_map(function(DTO\PreparedCookies $i) use ($isHttps, $setDomen) {
            $cookie = new Web\Cookie($i->key, $i->value, time() + 604800, false);
            $cookie->setSecure($isHttps);
            $cookie->setDomain($setDomen);
            $cookie->setHttpOnly(true);
            $cookie->setPath('/');

            return $cookie;
        }, $params);

        foreach ($cookies as $cookie) {
            $context->getResponse()->addCookie($cookie);
        }

        $context->getResponse()->writeHeaders("");
    }
}