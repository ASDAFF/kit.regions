<?php

namespace Kit\Regions\System\Traits;

use Bitrix\Main\Context;
use Kit\Regions\DTO;
use Bitrix\Sale\Location as SaleLocation;
use Bitrix\Main\DB;

trait getAllLocationTrait
{
    private function getAllLocation(array $actions, int $countryId): DTO\ResponseData
    {
        $result = new DTO\ResponseData();

        $context = Context::getCurrent();

        if ($countryId !== 0) {
            $country = SaleLocation\LocationTable::query()
                ->addSelect('ID')
                ->addSelect('NAME.NAME', 'COUNTRY_NAME')
                ->addSelect('LEFT_MARGIN')
                ->addSelect('RIGHT_MARGIN')
                ->addOrder('ID')
                ->where('PARENT_ID', 0)
                ->where('NAME.LANGUAGE_ID', $context->getLanguage())
                ->fetchAll();
            ;
            $currentCountry = array_reduce($country, function(array $curry, array $i) use($countryId) {
                return (int)$i['ID'] === $countryId ? $i : $curry;
            }, current($country));

            $result->locationTemplateData = [];
            $result->locationTemplateData['country'] = array_column($country, 'COUNTRY_NAME', 'ID');
            $result->locationTemplateData['activ'] = $currentCountry['ID'];
        }

        $allLocationsPrepare = SaleLocation\LocationTable::query()
            ->addSelect('ID')
            ->addSelect('NAME.NAME', 'CITY_NAME')
            ->addOrder('DEPTH_LEVEL')
            ->where('NAME.LANGUAGE_ID', $context->getLanguage());

        $type = SaleLocation\TypeTable::query()
            ->setSelect(['ID'])
            ->where('CODE', 'CITY')
            ->fetch();

        if (is_array($type)) {
            $allLocationsPrepare->where('TYPE_ID', $type['ID']);
        }

        if (is_array($currentCountry) && count($currentCountry) > 0) {
            $allLocationsPrepare
                ->where('LEFT_MARGIN', '>=', $currentCountry['LEFT_MARGIN'])
                ->where('RIGHT_MARGIN', '<=', $currentCountry['RIGHT_MARGIN'])
            ;
        }

        $allLocations = array_column((clone $allLocationsPrepare)->fetchAll(), 'CITY_NAME', 'ID');

        if (is_array($currentCountry) && count($currentCountry) > 0) {
            $context = Context::getCurrent();

            $bigCityCode = SaleLocation\DefaultSiteTable::query()
                ->addSelect('LOCATION_CODE')
                ->where('SITE_ID', $context->getSite())
                ->fetchAll()
            ;

            $bigCityArray = $allLocationsPrepare
                ->whereIn('CODE', array_column($bigCityCode, 'LOCATION_CODE'))
                ->fetchAll()
            ;

            $bigCity = array_column($bigCityArray, 'CITY_NAME', 'ID');
            $result->locationTemplateData['bigCity'] = $bigCity;
        }

        $result->actions = $actions;
        $result->allRegions = $allLocations;

        return $result;
    }
}