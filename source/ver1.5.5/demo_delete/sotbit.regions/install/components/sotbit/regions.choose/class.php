<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Sotbit\Regions\Config\Option;
use Sotbit\Regions\Internals\RegionsTable;
use Sotbit\Regions\Location;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Class SotbitRegionsChooseComponent
 *
 * @author Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class SotbitRegionsChooseComponent extends \CBitrixComponent
{

    static $mainDomain = "";

    public function setMainDomain(){
        $context = Application::getInstance()->getContext();
        $server = $context->getServer();
        //$domainName = (!empty($_SERVER['HTTPS']) && 'off' !== mb_strtolower($_SERVER['HTTPS'])?"https://":"http://") . $_SERVER['SERVER_NAME'];

        self::$mainDomain = RegionsTable::GetList(array(
            'order'  => array("ID" => "asc"),
            'select' => array("CODE"),
            'filter' => array(
                "DEFAULT_DOMAIN" => "Y",
                "SITE_ID" => serialize(array(0 => SITE_ID))
            )
        ))->fetch();

        /*
        foreach (self::$mainDomain as $domain) {
            if (str_replace(['https://', 'http://'], '', $domain) == $_SERVER['SERVER_NAME']) {
                self::$mainDomain = $_SERVER['SERVER_NAME'];
            }
        */
        //self::$mainDomain =
        //"CODE" => $domainName

        if(!self::$mainDomain)
            self::$mainDomain[0] = $server->getHttpHost();
        else if(!empty(self::$mainDomain['CODE'])){
            self::$mainDomain[0] = str_replace(
                ['https://', 'http://'],
                '',
                self::$mainDomain['CODE']
            );
        }
    }
    /**
     * @return mixed|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {
        if (Loader::includeModule('sotbit.regions'))
        {
            $this->arParams['FROM_LOCATION'] = Option::get('MODE_LOCATION', SITE_ID);
            if (!Loader::includeModule('sale') ) {//&& SITE_SERVER_NAME != $_SERVER['SERVER_NAME'] ) {
                $this->arParams['FROM_LOCATION'] = 'N';
            }

            $domain = new Location\Domain();

            self::setMainDomain();
            $this->setRegionsList($domain);

            if ($this->arParams['FROM_LOCATION'] != 'Y' || !$this->arResult['USER_REGION_NAME'])
            {
                if (Option::get('SINGLE_DOMAIN', SITE_ID) != 'Y')
                {
                    if(isset($domain->getProp('REAL_REGION')['NAME']))
                    {
                        $this->arResult['USER_REGION_ID'] = $domain->getProp('REAL_REGION')['ID'];
                        $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $domain->getProp('REAL_REGION')['NAME'];
                    }else{
                        foreach ($this->arResult['REGION_LIST'] as $region)
                        {
                            if ($region['CURRENT'])
                            {
                                $this->arResult['USER_REGION_ID'] = $region['ID'];
                                $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $region['NAME'];
                                break;
                            }
                        }
                    }

                } else {
                    if ($_COOKIE['sotbit_regions_id'] > 0)
                    {
                        $this->arResult['USER_REGION_ID'] = $_COOKIE['sotbit_regions_id'];
                        foreach ($this->arResult['REGION_LIST'] as $region)
                        {
                            if ($region['ID'] == $this->arResult['USER_REGION_ID'])
                            {
                                $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $region['NAME'];
                            }
                        }
                    } else {
                        foreach ($this->arResult['REGION_LIST'] as $region)
                        {
                            if ($region['CURRENT'])
                            {
                                $this->arResult['USER_REGION_ID'] = $region['ID'];
                                $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $region['NAME'];
                            }
                        }
                        if (!$this->arResult['USER_REGION_ID'])
                        {
                            $this->arResult['USER_REGION_ID'] = $this->arResult['REGION_LIST'][0]['ID'];
                            $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $this->arResult['REGION_LIST'][0]['NAME'];
                        }

                    }
                }
            }
            else{
                foreach ($this->arResult['REGION_LIST'] as $region)
                {
                    if ($region['CURRENT'])
                    {
                        $this->arResult['USER_REGION_ID'] = $region['ID'];
                        $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $region['NAME'];
                    }
                }
            }
//            $context = Application::getInstance()->getContext();
//            $server = $context->getServer();
//            $mainDomain = RegionsTable::GetList(array(
//                'order'  => array("ID" => "asc"),
//                'select' => array("CODE"),
//                'filter' => array("DEFAULT_DOMAIN" => "Y")
//            ))->fetch();
//            if(!$mainDomain)
//                $mainDomain[0] = $server->getHttpHost();
//            else if(!empty($mainDomain['CODE']))
//                $mainDomain[0] = $mainDomain['CODE'];
//            $serverDomain = $server->getHttpHost();
//            $serverDomain=explode(':',$serverDomain);
//            $serverDomain = $serverDomain[0];
//            $this->arResult['ROOT_DOMAIN'] = $serverDomain;
            $this->arResult['ROOT_DOMAIN'] = self::$mainDomain;

            if (Option::get('SINGLE_DOMAIN', SITE_ID) != 'Y')
            {
//                $arDomain = explode('.', $this->arResult['ROOT_DOMAIN']);
//                $count = count($arDomain);
//                $rDomain = $arDomain[$count - 2].'.'.$arDomain[$count - 1];
//                $rDomain = str_replace(
//                    ['https://', 'http://'],
//                    '',
//                    $rDomain
//                );
//                $rDomain = $this->arResult['ROOT_DOMAIN'];
//                $rDomain = str_replace(
//                    ['https://', 'http://'],
//                    '',
//                    $rDomain
//                );
//                $this->arResult['ROOT_DOMAIN'] = $rDomain;
                $this->arResult['ROOT_DOMAIN'] = self::$mainDomain;
            }


            if ($this->needChooseLocation()) {
                $this->arResult['SHOW_POPUP'] = 'Y';
            } else {
                $this->arResult['SHOW_POPUP'] = 'N';

            }

            $this->includeComponentTemplate();
        }
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\SystemException
     */
    private function needChooseLocation()
    {
        $return = true;
        if ($_COOKIE['sotbit_regions_city_choosed'] == 'Y') {
            $return = false;
        }

        return $return;
    }

    /**
     * @param Location\Domain $domain
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    private function setRegionsList(\Sotbit\Regions\Location\Domain $domain)
    {
//        $mainDomain = RegionsTable::GetList(array(
//            'order'  => array("ID" => "asc"),
//            'select' => array("CODE"),
//            'filter' => array("DEFAULT_DOMAIN" => "Y")
//        ))->fetch();
//
//        $mainDomain['CODE'] = str_replace(
//            ['https://', 'http://'],
//            '',
//            $mainDomain['CODE']
//        );
        if ($this->arParams['FROM_LOCATION'] == 'Y'  && Loader::includeModule('sale')/* && self::$mainDomain[0] == $_SERVER['SERVER_NAME']*/ //&& SITE_SERVER_NAME == $_SERVER['SERVER_NAME']
        ) {
            $this->setRegionsListFromLocation($domain);
        } else {
            $this->setRegionsListFromList($domain);
        }
    }

    /**
     * @param Location\Domain $domain
     *
     * @throws \Bitrix\Main\ArgumentException
     */
    private function setRegionsListFromLocation(
        \Sotbit\Regions\Location\Domain $domain
    ) {
        $return = [];
        //$favoriteCodes = $this->getFavoriteLocationCodes();
        $currentId = $domain->getProp('LOCATION')['ID'];
        $currentName = $domain->getProp('LOCATION')['SALE_LOCATION_LOCATION_NAME_NAME'];
        $this->arResult['FAVORITES'] = [];
        $this->arResult['TITLE_CITIES'] = [];
        $this->arResult['REGION_LIST'] = [];

        if($currentId > 0)
        {
            $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $currentName;
            $this->arResult['USER_REGION_ID'] = $currentId;
            $this->arResult['USER_MULTI_REGION_ID'] = $currentId = isset($domain->getProp('REAL_REGION')['ID']) ? $domain->getProp('REAL_REGION')['ID'] : $domain->getProp('ID');
            $this->arResult['USER_REGION_CODE'] = isset($domain->getProp('REAL_REGION')['CODE']) ? $domain->getProp('REAL_REGION')['CODE'] : $domain->getProp('CODE');
        }

        /*

        if($currentId > 0)
        {
            $rs = \Bitrix\Sale\Location\LocationTable::getList(
                [
                    'filter' => [
                        'ID' => $currentId,
                        '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    ],
                    'order'  => [
                        'SORT'=>'asc',
                        'TYPE_ID' => 'asc',
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
                $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
            }
        }else{
            $name = $domain->getProp('NAME');
            $rs = \Bitrix\Sale\Location\LocationTable::getList(
                [
                    'filter' => [
                        'NAME.NAME' => $name,
                        '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    ],
                    'order'  => [
                        'SORT'=>'asc',
                        'TYPE_ID' => 'asc',
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
            while ($location = $rs->fetch()) {
                $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
            }
        }

        /*
                $rs = \Bitrix\Sale\Location\LocationTable::getList(
                    [
                        'filter' => [
                            'TYPE_ID'           => [1, 5],
                            '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                        ],
                        'order'  => [
                            'SORT'=>'asc',
                            'TYPE_ID' => 'asc',
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
                while ($location = $rs->fetch()) {
                    if($currentId > 0){
                        if ($location['ID'] == $currentId) {
                            $location['CURRENT'] = 'Y';
                            $this->arResult['USER_REGION_NAME']
                                = $this->arResult['USER_REGION_NAME_LOCATION']
                                = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                            $fullName
                                = \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById(
                                $location['ID'],
                                ['INVERSE' => true]
                            );

                            $pos = strpos($fullName, ',');
                            $this->arResult['USER_REGION_FULL_NAME'] = '<span data-location-id="'
                                .$location['ID'].'">'.substr($fullName, 0, $pos + 1)
                                .'</span>'.substr($fullName, $pos + 1, mb_strlen($fullName));
                        }
                    }
                    else{
                        $name = $domain->getProp('NAME');
                        if($location['SALE_LOCATION_LOCATION_NAME_NAME'] == $name){
                            $location['CURRENT'] = 'Y';
                            $this->arResult['USER_REGION_NAME']
                                = $this->arResult['USER_REGION_NAME_LOCATION']
                                = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                            $fullName
                                = \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById(
                                $location['ID'],
                                ['INVERSE' => true]
                            );

                            $pos = strpos($fullName, ',');
                            $this->arResult['USER_REGION_FULL_NAME'] = '<span data-location-id="'
                                .$location['ID'].'">'.substr($fullName, 0, $pos + 1)
                                .'</span>'.substr($fullName, $pos + 1, mb_strlen($fullName));
                        }
                    }

                    if ($location['TYPE_ID'] == 1) {
                        $return[$location['ID']] = $location;
                        $return[$location['ID']]['CITY'] = [];
                    } else {
                        if (isset($return[$location['COUNTRY_ID']]['CITY'])) {
                            if (in_array($location['CODE'], $favoriteCodes)) {
                                $this->arResult['FAVORITES'][$location['COUNTRY_ID']][]
                                    = $location;
                            }
                            if (
                                $location['SALE_LOCATION_LOCATION_NAME_NAME']
                                == \Bitrix\Main\Localization\Loc::getMessage('SOTBIT_REGIONS_MOSCOW')
                                || $location['SALE_LOCATION_LOCATION_NAME_NAME']
                                == \Bitrix\Main\Localization\Loc::getMessage('SOTBIT_REGIONS_SP')

                            ) {
                                $this->arResult['TITLE_CITIES'][] = $location;
                            }
                            $letter = substr(
                                $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                                0,
                                1
                            );
                            $return[$location['COUNTRY_ID']]['CITY'][$letter][]
                                = $location;

                            if (!in_array($location['CODE'], $favoriteCodes)) {
                                $this->arResult['REGION_LIST'][] = [
                                    'ID'   => $location['ID'],
                                    'NAME' => $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                                ];
                                if($location['CURRENT'] == 'Y'){
                                    $this->arResult['USER_REGION_NAME'] = $this->arResult['USER_REGION_NAME_LOCATION'] = $location['SALE_LOCATION_LOCATION_NAME_NAME'];
                                    $this->arResult['USER_REGION_ID'] = $location['ID'];
                                }
                            }
                        }
                    }
                }
                if ($this->arResult['REGION_LIST'] && $this->arResult['FAVORITES']) {
                    $favorites = [];
                    foreach($this->arResult['FAVORITES'] as $country){
                        foreach ($country as $favorite){
                            $favorites[] = [
                                'ID' => $favorite['ID'],
                                'NAME' => $favorite['SALE_LOCATION_LOCATION_NAME_NAME'],
                            ];
                        }
                    }
                    $this->arResult['REGION_LIST'] = array_merge($favorites,$this->arResult['REGION_LIST']);
                }
                $this->arResult['REGION_LIST_COUNTRIES'] = $return;*/
    }

    /**
     * @param Location\Domain $domain
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    protected function setRegionsListFromList(
        \Sotbit\Regions\Location\Domain $domain
    ) {
        $return = [];
        $context = Application::getInstance()->getContext();
        $server = $context->getServer();
        $requestUri = $server->getRequestUri();
        $i = 0;
        $this->arResult['REGION_LIST_COUNTRIES'] = [0 => ['CITY' => []]];
        $rs = RegionsTable::getList([
            'order'  => [
                'SORT' => 'asc',
            ],
            'filter' => ['%SITE_ID' => SITE_ID],
            'cache'  => [
                'ttl' => 36000000,
            ],
        ]);
        while ($region = $rs->fetch())
        {
            $return[$i]['ID'] = $region['ID'];
            $return[$i]['NAME'] = $region['NAME'];
            $return[$i]['URL'] = $region['CODE'].$requestUri;
            $return[$i]['CODE'] = $region['CODE'];
            $return[$i]['DOMAIN'] = str_replace(array('www.', 'https://', 'http://', '/'), '', $region['CODE']);
            if ($region['ID'] == $domain->getProp('ID')) {
                $return[$i]['CURRENT'] = 'Y';
            }
            ++$i;

            $letter = mb_substr(
                $region['NAME'],
                0,
                1,
                LANG_CHARSET
            );
            $this->arResult['REGION_LIST_COUNTRIES'][0]['CITY'][$letter][] = [
                'ID' => $region['ID'],
                'SALE_LOCATION_LOCATION_NAME_NAME' => $region['NAME']
            ];

        }

        $this->arResult['REGION_LIST'] = $return;
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