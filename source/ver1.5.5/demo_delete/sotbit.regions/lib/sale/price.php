<?php

namespace Sotbit\Regions\Sale;

use Bitrix\Catalog\GroupTable;
use Bitrix\Main\Loader;

/**
 * Class Price
 *
 * @package Sotbit\Regions\Sale
 * @author  Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class Price
{
    /**
     * get id prices for region
     *
     * @return array
     */
    public function getPriceIds()
    {
        $return = [];
        if ($_SESSION['SOTBIT_REGIONS']['PRICE_CODE']) {
            $rs = GroupTable::getList(
                [
                    'filter' => [
                        'NAME' => $_SESSION['SOTBIT_REGIONS']['PRICE_CODE'],
                    ],
                    'select' => ['ID'],
                    'cache'  => [
                        'ttl' => 36000000,
                    ],
                ]
            );
            while ($priceType = $rs->Fetch()) {
                $return[$priceType['ID']] = $priceType['ID'];
            }
        }

        return $return;
    }

    /**
     * @param array $arResult
     *
     * @return array
     */
    public function change($arResult = [])
    {
        $priceIds = self::getPriceIds();
        if (isset($arResult['ITEMS'])) {
            foreach ($arResult['ITEMS'] as $i => $item) {
                $arResult['ITEMS'][$i] = self::changeElPrice($item, $priceIds);
                if (isset($Item['OFFERS'])) {
                    foreach ($item['OFFERS'] as $k => $offer) {
                        $arResult['ITEMS'][$i]['OFFERS'][$k]
                            = self::changeElPrice($offer, $priceIds);
                    }
                }
            }
        }

        if (isset($arResult['OFFERS'])) {
            foreach ($arResult['OFFERS'] as $i => $offer) {
                $arResult['OFFERS'][$i] = self::changeElPrice($offer,
                    $priceIds);
            }
        }

        if (isset($arResult['JS_OFFERS'])) {
            foreach ($arResult['JS_OFFERS'] as $i => $offer) {
                $arResult['JS_OFFERS'][$i] = self::changeElPrice($offer,
                    $priceIds);
            }
        }

        $arResult = self::changeElPrice($arResult, $priceIds);

        return $arResult;
    }

    /**
     * @param array $el
     * @param array $priceIds
     *
     * @return array
     */
    public function changeElPrice($el = [], $priceIds = [])
    {
        if ($el['MIN_PRICE']
            && in_array($el['MIN_PRICE']['PRICE_ID'], $priceIds)
        ) {
            $el['MIN_PRICE'] = self::changeMin($el['MIN_PRICE']);;
        }
        if ($el['PRICES']) {
            foreach ($el['PRICES'] as $i => $price) {
                if (in_array($price['PRICE_ID'], $priceIds)
                    || in_array($price['PRICE_TYPE_ID'], $priceIds)
                ) {
                    $el['PRICES'][$i] = self::changeMin($el['PRICES'][$i]);
                }
            }
        }
        if ($el['ITEM_PRICES']) {
            foreach ($el['ITEM_PRICES'] as $i => $price) {
                if (in_array($price['PRICE_ID'], $priceIds)
                    || in_array($price['PRICE_TYPE_ID'], $priceIds)
                ) {
                    $el['ITEM_PRICES'][$i]
                        = self::changeMin($el['ITEM_PRICES'][$i]);
                }
            }
        }
        if ($el['ALL_PRICES']) {
            foreach ($el['ALL_PRICES'] as $i => $price) {
                if (in_array($price['CATALOG_GROUP_ID'], $priceIds)) {
                    $el['ALL_PRICES'][$i]
                        = self::changeMin($el['ALL_PRICES'][$i]);
                }
            }
        }

        return $el;
    }

    /**
     * @param array $price
     *
     * @return array
     */
    private function changeMin($price = [])
    {
        if ($_SESSION['SOTBIT_REGIONS']['PRICE_VALUE']) {
            switch ($_SESSION['SOTBIT_REGIONS']['PRICE_VALUE_TYPE']) {
                case 'PROCENT_UP':
                    if (isset($price['VALUE'])) {
                        $price['VALUE'] = $price['VALUE'] + ($price['VALUE']
                                / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_VALUE'] = \CurrencyFormat($price['VALUE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['DISCOUNT_VALUE'])) {
                        $price['DISCOUNT_VALUE'] = $price['DISCOUNT_VALUE']
                            + ($price['DISCOUNT_VALUE'] / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_DISCOUNT_VALUE']
                            = \CurrencyFormat($price['DISCOUNT_VALUE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['DISCOUNT_PRICE'])) {
                        $price['DISCOUNT_PRICE'] = $price['DISCOUNT_PRICE']
                            + ($price['DISCOUNT_PRICE'] / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_DISCOUNT_PRICE']
                            = \CurrencyFormat($price['DISCOUNT_PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['PRICE'])) {
                        $price['PRICE'] = $price['PRICE'] + ($price['PRICE']
                                / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_PRICE'] = \CurrencyFormat($price['PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['BASE_PRICE'])) {
                        $price['BASE_PRICE'] = $price['BASE_PRICE']
                            + ($price['BASE_PRICE'] / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_BASE_PRICE']
                            = \CurrencyFormat($price['BASE_PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['RATIO_PRICE'])) {
                        $price['RATIO_PRICE'] = $price['RATIO_PRICE']
                            + ($price['RATIO_PRICE'] / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_RATIO_PRICE']
                            = \CurrencyFormat($price['RATIO_PRICE'],
                            $price['CURRENCY']);
                    }
                    break;
                case 'PROCENT_DOWN':
                    if (isset($price['VALUE'])) {
                        $price['VALUE'] = $price['VALUE'] - ($price['VALUE']
                                / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_VALUE'] = \CurrencyFormat($price['VALUE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['DISCOUNT_VALUE'])) {
                        $price['DISCOUNT_VALUE'] = $price['DISCOUNT_VALUE']
                            - ($price['DISCOUNT_VALUE'] / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_DISCOUNT_VALUE']
                            = \CurrencyFormat($price['DISCOUNT_VALUE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['DISCOUNT_PRICE'])) {
                        $price['DISCOUNT_PRICE'] = $price['DISCOUNT_PRICE']
                            - ($price['DISCOUNT_PRICE'] / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_DISCOUNT_PRICE']
                            = \CurrencyFormat($price['DISCOUNT_PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['PRICE'])) {
                        $price['PRICE'] = $price['PRICE'] - ($price['PRICE']
                                / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_PRICE'] = \CurrencyFormat($price['PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['BASE_PRICE'])) {
                        $price['BASE_PRICE'] = $price['BASE_PRICE']
                            - ($price['BASE_PRICE'] / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_BASE_PRICE']
                            = \CurrencyFormat($price['BASE_PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['RATIO_PRICE'])) {
                        $price['RATIO_PRICE'] = $price['RATIO_PRICE']
                            - ($price['RATIO_PRICE'] / 100)
                            * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_RATIO_PRICE']
                            = \CurrencyFormat($price['RATIO_PRICE'],
                            $price['CURRENCY']);
                    }
                    break;
                case 'FIX_UP':
                    if (isset($price['VALUE'])) {
                        $price['VALUE'] = $price['VALUE']
                            + $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_VALUE'] = \CurrencyFormat($price['VALUE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['DISCOUNT_VALUE'])) {
                        $price['DISCOUNT_VALUE'] = $price['DISCOUNT_VALUE']
                            + $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_DISCOUNT_VALUE']
                            = \CurrencyFormat($price['DISCOUNT_VALUE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['DISCOUNT_PRICE'])) {
                        $price['DISCOUNT_PRICE'] = $price['DISCOUNT_PRICE']
                            + $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_DISCOUNT_PRICE']
                            = \CurrencyFormat($price['DISCOUNT_PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['PRICE'])) {
                        $price['PRICE'] = $price['PRICE']
                            + $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_PRICE'] = \CurrencyFormat($price['PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['BASE_PRICE'])) {
                        $price['BASE_PRICE'] = $price['BASE_PRICE']
                            + $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_BASE_PRICE']
                            = \CurrencyFormat($price['BASE_PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['RATIO_PRICE'])) {
                        $price['RATIO_PRICE'] = $price['RATIO_PRICE']
                            + $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_RATIO_PRICE']
                            = \CurrencyFormat($price['RATIO_PRICE'],
                            $price['CURRENCY']);
                    }
                    break;
                case 'FIX_DOWN':
                    if (isset($price['VALUE'])) {
                        $price['VALUE'] = $price['VALUE']
                            - $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_VALUE'] = \CurrencyFormat($price['VALUE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['DISCOUNT_VALUE'])) {
                        $price['DISCOUNT_VALUE'] = $price['DISCOUNT_VALUE']
                            - $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_DISCOUNT_VALUE']
                            = \CurrencyFormat($price['DISCOUNT_VALUE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['DISCOUNT_PRICE'])) {
                        $price['DISCOUNT_PRICE'] = $price['DISCOUNT_PRICE']
                            - $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_DISCOUNT_PRICE']
                            = \CurrencyFormat($price['DISCOUNT_PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['PRICE'])) {
                        $price['PRICE'] = $price['PRICE']
                            - $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_PRICE'] = \CurrencyFormat($price['PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['BASE_PRICE'])) {
                        $price['BASE_PRICE'] = $price['BASE_PRICE']
                            - $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_BASE_PRICE']
                            = \CurrencyFormat($price['BASE_PRICE'],
                            $price['CURRENCY']);
                    }
                    if (isset($price['RATIO_PRICE'])) {
                        $price['RATIO_PRICE'] = $price['RATIO_PRICE']
                            - $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                        $price['PRINT_RATIO_PRICE']
                            = \CurrencyFormat($price['RATIO_PRICE'],
                            $price['CURRENCY']);
                    }
                    break;
            }
        }

        return $price;
    }

    /**
     * @throws \Bitrix\Main\LoaderException
     */
    public function changeBasket()
    {
        if (Loader::includeModule("sale")
            && Loader::includeModule("catalog")
        ) {
            global $USER;

            if (!is_object($USER)) {
                $USER = new \CUser;
            }

            $result = \Bitrix\Sale\Internals\BasketTable::getList(
                [
                    'select' => [
                        'BASE_PRICE',
                        'PRICE',
                        'PRODUCT_ID',
                        'LID',
                        'ID',
                        'PRODUCT_PRICE_ID',
                        'CURRENCY'
                    ],
                    'filter' => [
                        'LID'      => SITE_ID,
                        'FUSER_ID' => \CSaleBasket::GetBasketUserID(),
                        "ORDER_ID" => "NULL",
                    ],
                ]
            );

            while ($BasketItem = $result->fetch()) {
                $dbPrice = \CPrice::GetList([
                    "QUANTITY_FROM" => "ASC",
                    "QUANTITY_TO"   => "ASC",
                    "SORT"          => "ASC",
                ], [
                    "PRODUCT_ID" => $BasketItem['PRODUCT_ID'],
                    'ID'         => $BasketItem['PRODUCT_PRICE_ID'],
                ], false, false, [
                    "ID",
                    "CATALOG_GROUP_ID",
                    "PRICE",
                    "CURRENCY",
                ]);
                while ($arPrice = $dbPrice->Fetch()) {
                    if($BasketItem['CURRENCY']) {
                        if (
                            $arPrice['CURRENCY'] != $BasketItem['CURRENCY'] &&
                            Loader::includeModule('currency')
                        ) {
                            $arPrice['PRICE'] = \CCurrencyRates::ConvertCurrency($arPrice['PRICE'], $arPrice['CURRENCY'], $BasketItem['CURRENCY']);
                            $arPrice['CURRENCY'] = $BasketItem['CURRENCY'];
                            $arPrice['PRICE'] = \Bitrix\Catalog\Product\Price::roundPrice(
                                $arPrice['CATALOG_GROUP_ID'],
                                $arPrice['PRICE'],
                                $arPrice['CURRENCY']
                            );
                        }
                    }
                    $arDiscounts = \CCatalogDiscount::GetDiscountByPrice(
                        $arPrice["ID"],
                        $USER->GetUserGroupArray(),
                        "N",
                        $BasketItem['LID']
                    );
                    $price = \CCatalogProduct::CountPriceWithDiscount(
                        $arPrice["PRICE"],
                        $arPrice["CURRENCY"],
                        $arDiscounts
                    );

                    if ($_SESSION['SOTBIT_REGIONS']['PRICE_VALUE']) {
                        switch ($_SESSION['SOTBIT_REGIONS']['PRICE_VALUE_TYPE']) {
                            case 'PROCENT_UP':
                                $price = $price + ($price / 100)
                                    * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                                break;
                            case 'PROCENT_DOWN':
                                $price = $price - ($price / 100)
                                    * $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                                break;
                            case 'FIX_UP':
                                $price = $price
                                    + $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                                break;
                            case 'FIX_DOWN':
                                $price = $price
                                    - $_SESSION['SOTBIT_REGIONS']['PRICE_VALUE'];
                                break;
                        }
                    }
                    if($BasketItem['CURRENCY']){
                        if ($price && ($price != $BasketItem['PRICE'])) {
                            try {
                                \Bitrix\Sale\Internals\BasketTable::Update(
                                    $BasketItem['ID'],
                                    [
                                        'PRICE'        => $price,
                                        'CURRENCY'     => $BasketItem['CURRENCY'],
                                        'CUSTOM_PRICE' => 'Y',
                                    ]
                                );
                            } catch (\Exception $e) {
                            }
                        }
                    }
                }
            }
        }
    }
}