<?php

namespace Sotbit\Regions\Location\User;

use Bitrix\Main\Loader;

/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 07-Feb-18
 * Time: 2:29 PM
 */
class Statistic
{
    public function __construct(
    ) {
    }

    public function getUserCity(
    ) {
        if (!Loader::includeModule('statistic')) {
            return false;
        }
        $cityObj = new \CCity();
        $arThisCity = $cityObj->GetFullInfo();
        return $arThisCity['CITY_NAME']['VALUE'];
    }
}