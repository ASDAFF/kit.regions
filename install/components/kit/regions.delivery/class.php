<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Kit\Regions\Location;
use Bitrix\Sale\Delivery\Services\Table;
use Kit\Regions\Helper\LocationType;
use Kit\Regions\Sale;

Loc::loadMessages(__FILE__);

class KitRegionsDeliveryComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        if(!$arParams['CACHE_TIME']){
            $arParams['CACHE_TIME'] = 36000000;
        }
        if(!$arParams['CACHE_TYPE']){
            $arParams['CACHE_TYPE'] = 'A';
        }
        return $arParams;
    }
    public function executeComponent()
    {
        $moduleIncluded = false;
        try {
            if(!Loader::includeModule('kit.regions') ||
                !Loader::includeModule('sale') ||
                !Loader::includeModule('catalog')
            )
            {
                return false;
            }
        } catch (\Bitrix\Main\LoaderException $e) {
        }

        if($this->arParams['AJAX'] == 'Y')
        {
            $this->includeComponentTemplate();
            return false;
        }
        
        if(!$this->arParams['ELEMENT_ID']){
            return false;
        }
            $domain = new Location\Domain();

            if($this->arParams['LOCATION_TO'] > 0)
            {
                $currentCode = $this->arParams['LOCATION_TO'];
                $rs = \Bitrix\Sale\Location\LocationTable::getList(
                    [
                        'filter' => [
                            'TYPE_ID'           => [1, LocationType::getCity()],
                            'ID' => $this->arParams['LOCATION_TO'],
                        ],
                        'order'  => [
                            'SORT'      => 'asc',
                            'TYPE_ID'   => 'asc',
                            'NAME.NAME' => 'asc'
                        ],
                        'select' => [
                            'ID',
                            'CODE',
                            'COUNTRY_ID',
                            'TYPE_ID',
                            'NAME.NAME',
                        ],
                    ]
                );
                while($city = $rs->fetch()){
                    $currentCode = $city['CODE'];
                }
            }
            else{
                $currentCode = $domain->getProp('LOCATION')['CODE'];
            }

            if(!$currentCode)
            {
                $name = $domain->getProp('NAME');
                if($name){
                    $rs = \Bitrix\Sale\Location\LocationTable::getList(
                        [
                            'filter' => [
                                'TYPE_ID'           => LocationType::getCity(),
                                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                '=NAME.NAME' => $name
                            ],
                            'order'  => [
                                'SORT'      => 'asc',
                                'TYPE_ID'   => 'asc',
                                'NAME.NAME' => 'asc'
                            ],
                            'select' => [
                                'ID',
                                'CODE'
                            ],
                        ]
                    );
                    if($city = $rs->fetch()){
                        $currentCode = $city['CODE'];
                    }
                }
            }
            if(!$currentCode){
                return false;
            }

            //if ($this->startResultCache($this->arParams['CACHE_TIME'], ['ELEMENT_ID' => $this->arParams['ELEMENT_ID'],'CURRENT_CODE' => $currentCode],'/'.SITE_ID.'/kit/kit.regions'))
            {
                $this->arResult['RAND'] = $this->randString();
                if($this->arParams['AJAX'] != 'Y')
                {
                    $this->arResult['PRODUCT'] = [];
                    $rs = \Bitrix\Catalog\Model\Product::getList(
                        [
                            'filter' => ['ID' => $this->arParams['ELEMENT_ID']],
                            'select' => [
                                'WEIGHT',
                                'WIDTH',
                                'HEIGHT',
                                'LENGTH',
                                'MEASURE'
                            ],
                            'limit'  => 1
                        ]
                    );
                    while ($product = $rs->fetch())
                    {
                        if ($product['MEASURE'] > 0)
                        {
                            $product['MEASURE'] = \CCatalogMeasure::getList([],
                                ['ID' => $product['MEASURE']], false, false, ['*'])
                                ->fetch();
                        }
                        $this->arResult['PRODUCT'] = $product;
                    }


                    // calculation deliveries and pays
                    $calculationObj = new Sale\Calculation([
                        'currentCode' => $currentCode,
                        'elementId'   => $this->arParams['ELEMENT_ID'],
                        'limit'       => $this->arParams["LIMIT"]
                    ]);

                    // calculation deliveries
                    $this->arResult['DELIVERY'] = $calculationObj->getDelivery();

                    // calculation payments
                    $this->arResult['PAYMENT'] = $calculationObj->getPaySystem();

                    $this->arResult['CURRENT'] = $currentCode;

                    $this->arResult['TITLE_CITIES'] = [];
                    //$favoriteCodes = $this->getFavoriteLocationCodes();

                    $rs = \Bitrix\Sale\Location\LocationTable::getList(
                        [
                            'filter' => [
                                'TYPE_ID'           => [1, LocationType::getCity()],
                                'CODE' => $currentCode,
                                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                            ],
                            'order'  => [
                                'SORT'      => 'asc',
                                'TYPE_ID'   => 'asc',
                                'NAME.NAME' => 'asc'
                            ],
                            'select' => [
                                'ID',
                                'CODE',
                                'COUNTRY_ID',
                                'TYPE_ID',
                                'NAME.NAME',
                                'PARENT.COUNTRY_ID',
                            ],
                        ]
                    );
                    while ($location = $rs->fetch())
                    {
                        if ($location['CODE'] == $currentCode)
                        {
                            $location['CURRENT'] = 'Y';
                            $this->arResult['USER_REGION_NAME']
                                = $this->arResult['USER_REGION_NAME_LOCATION']
                                = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                            $fullName
                                = \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById(
                                $location['ID'],
                                ['INVERSE' => true]
                            );

                            $pos = mb_strpos($fullName, ',');
                            $this->arResult['USER_REGION_FULL_NAME']
                                = '<span data-entity="item" data-index="'
                                .$location['ID'].'">'.mb_substr($fullName, 0, $pos + 1, LANG_CHARSET)
                                .'</span>'.mb_substr($fullName, $pos + 1,
                                    mb_strlen($fullName), LANG_CHARSET);
                        }

/*
                        if ($location['TYPE_ID'] == 1) {
                            $this->arResult['REGION_LIST_COUNTRIES'][$location['ID']]
                                = $location;
                            $this->arResult['REGION_LIST_COUNTRIES'][$location['ID']]['CITY']
                                = [];
                        } else {
                            if (isset($this->arResult['REGION_LIST_COUNTRIES'][$location['COUNTRY_ID']]['CITY'])) {
                                if (in_array($location['CODE'], $favoriteCodes)) {
                                    $this->arResult['FAVORITES'][$location['COUNTRY_ID']][]
                                        = $location;
                                }
                                if ($location['SALE_LOCATION_LOCATION_NAME_NAME']
                                    == Loc::getMessage('KIT_REGIONS_MOSCOW')
                                    || $location['SALE_LOCATION_LOCATION_NAME_NAME']
                                    == Loc::getMessage('KIT_REGIONS_SP')
                                ) {
                                    $this->arResult['TITLE_CITIES'][] = $location;
                                }
                                $letter = substr(
                                    $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                                    0,
                                    1
                                );
                                $this->arResult['REGION_LIST_COUNTRIES'][$location['COUNTRY_ID']]['CITY'][$letter][]
                                    = $location;
                            }
                        }*/
                    }
                }
            $this->includeComponentTemplate();
        }
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function getFavoriteLocationCodes()
    {
        $return = [];
        $rs = \Bitrix\Sale\Location\DefaultSiteTable::getList(
            [
                'select' => ['LOCATION_CODE'],
                'filter' => ['SITE_ID' => SITE_ID]
            ]
        );
        while ($location = $rs->fetch()) {
            $return[$location['LOCATION_CODE']] = $location['LOCATION_CODE'];
        }

        return $return;
    }
}
?>