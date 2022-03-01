<?php

namespace Kit\Regions\Sale;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Loader;
use Bitrix\Main\EventResult;
use \Bitrix\Main\Event;
use Bitrix\Sale\PropertyValue;
use Bitrix\Main;
use Bitrix\Catalog;
use \Bitrix\Main\Localization\Loc;

/**
 * Class EventHandlers
 * @package Kit\Regions\Sale
 */
class EventHandlers
{
	/**
	 * method equal CCatalogProduct::GetOptimalPrice with our price types
	 * @param $productId
	 * @param int $qnt
	 * @param array $userGroups
	 * @param string $renewal
	 * @param array $prices
	 * @param bool $siteId
	 * @param bool $coupons
	 * @return array|bool
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 */
	public static function OnGetOptimalPriceHandler(
		$productId,
		$qnt = 1,
		$userGroups = [],
		$renewal = "N",
		$prices = [],
		$siteId = false,
		$coupons = false
	)
	{
		if(!Loader::includeModule('kit.regions') || !Loader::includeModule('catalog') ||
			!$_SESSION['KIT_REGIONS']['PRICE_CODE'])
		{
			return true;
		}
		global $APPLICATION;
		$productId = (int)$productId;

		if (!is_array($userGroups) && (int)$userGroups.'|' == (string)$userGroups.'|')
			$userGroups = array((int)$userGroups);

		if (!is_array($userGroups))
			$userGroups = array();

		if (!in_array(2, $userGroups))
			$userGroups[] = 2;

		Main\Type\Collection::normalizeArrayValuesByInt($userGroups);

		$renewal = ($renewal == 'Y' ? 'Y' : 'N');

		if ($siteId === false)
			$siteId = SITE_ID;

		$resultCurrency = Catalog\Product\Price\Calculation::getCurrency();
		if (empty($resultCurrency))
		{
			$APPLICATION->ThrowException(Loc::getMessage("BT_MOD_CATALOG_PROD_ERR_NO_RESULT_CURRENCY"));
			return false;
		}

		$intIBlockID = (int)\CIBlockElement::GetIBlockByID($productId);
		if ($intIBlockID <= 0)
		{
			$APPLICATION->ThrowException(
				Loc::getMessage(
					'BT_MOD_CATALOG_PROD_ERR_ELEMENT_ID_NOT_FOUND',
					array('#ID#' => $productId)
				),
				'NO_ELEMENT'
			);
			return false;
		}

		if (!isset($prices) || !is_array($prices))
			$prices = array();

		if (empty($prices))
		{
			$priceTypeList = [];
			$priceIterator = Catalog\GroupAccessTable::getList(array(
				'select' => array('CATALOG_GROUP_ID'),
				'filter' => array('@GROUP_ID' => $userGroups, '=ACCESS' => Catalog\GroupAccessTable::ACCESS_BUY),
				'order' => array('CATALOG_GROUP_ID' => 'ASC')
			));
			while ($priceType = $priceIterator->fetch())
			{
				$priceTypeId = (int)$priceType['CATALOG_GROUP_ID'];
				$priceTypeList[$priceTypeId] = $priceTypeId;
			}

			$price = new Price();
			$regionPriceIds = $price->getPriceIds();

			$priceTypeList = array_intersect($regionPriceIds,$priceTypeList);

			if (empty($priceTypeList))
			{
				return false;
			}

			$iterator = Catalog\PriceTable::getList(array(
				'select' => array('ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY'),
				'filter' => array(
					'=PRODUCT_ID' => $productId,
					'@CATALOG_GROUP_ID' => $priceTypeList,
					array(
						'LOGIC' => 'OR',
						'<=QUANTITY_FROM' => $qnt,
						'=QUANTITY_FROM' => null
					),
					array(
						'LOGIC' => 'OR',
						'>=QUANTITY_TO' => $qnt,
						'=QUANTITY_TO' => null
					)
				)
			));
			while ($row = $iterator->fetch())
			{
				$row['ELEMENT_IBLOCK_ID'] = $intIBlockID;
				$prices[] = $row;
			}
			unset($row, $iterator);
			unset($priceTypeList);
		}
		else
		{
			foreach (array_keys($prices) as $priceIndex)
				$prices[$priceIndex]['ELEMENT_IBLOCK_ID'] = $intIBlockID;
			unset($priceIndex);
		}

		if (empty($prices))
			return false;

		$iterator = \CCatalogProduct::GetVATInfo($productId);
		$vat = $iterator->Fetch();
		if (!empty($vat))
		{
			$vat['RATE'] = (float)$vat['RATE'] * 0.01;
		}
		else
		{
			$vat = array('RATE' => 0.0, 'VAT_INCLUDED' => 'N');
		}
		unset($iterator);

		if(class_exists('\Bitrix\Catalog\Product\Price\Calculation::isAllowedUseDiscounts()'))
		{
			$isNeedDiscounts = Catalog\Product\Price\Calculation::isAllowedUseDiscounts();
			$resultWithVat = Catalog\Product\Price\Calculation::isIncludingVat();
		}
		else
		{
			$isNeedDiscounts = true;
			$resultWithVat = true;
		}

		if ($isNeedDiscounts)
		{
			if ($coupons === false)
				$coupons = \CCatalogDiscountCoupon::GetCoupons();
		}

		$boolDiscountVat = true;

		$minimalPrice = array();

		foreach ($prices as $priceData)
		{
			$priceData['VAT_RATE'] = $vat['RATE'];
			$priceData['VAT_INCLUDED'] = $vat['VAT_INCLUDED'];

			$currentPrice = (float)$priceData['PRICE'];
			if ($boolDiscountVat)
			{
				if ($priceData['VAT_INCLUDED'] == 'N')
					$currentPrice *= (1 + $priceData['VAT_RATE']);
			}
			else
			{
				if ($priceData['VAT_INCLUDED'] == 'Y')
					$currentPrice /= (1 + $priceData['VAT_RATE']);
			}
			if ($priceData['CURRENCY'] != $resultCurrency)
				$currentPrice = \CCurrencyRates::ConvertCurrency($currentPrice, $priceData['CURRENCY'],
					$resultCurrency);
			$currentPrice = Catalog\Product\Price\Calculation::roundPrecision($currentPrice);

			$result = array(
				'BASE_PRICE' => $currentPrice,
				'COMPARE_PRICE' => $currentPrice,
				'PRICE' => $currentPrice,
				'CURRENCY' => $resultCurrency,
				'DISCOUNT_LIST' => array(),
				'RAW_PRICE' => $priceData
			);
			if ($isNeedDiscounts)
			{
				$arDiscounts = \CCatalogDiscount::GetDiscount(
					$productId,
					$intIBlockID,
					$priceData['CATALOG_GROUP_ID'],
					$userGroups,
					$renewal,
					$siteId,
					$coupons
				);

				$discountResult = \CCatalogDiscount::applyDiscountList($currentPrice, $resultCurrency, $arDiscounts);
				unset($arDiscounts);
				if ($discountResult === false)
					return false;
				$result['PRICE'] = $discountResult['PRICE'];
				$result['COMPARE_PRICE'] = $discountResult['PRICE'];
				$result['DISCOUNT_LIST'] = $discountResult['DISCOUNT_LIST'];
				unset($discountResult);
			}

			if ($boolDiscountVat)
			{
				if (!$resultWithVat)
				{
					$result['PRICE'] /= (1 + $priceData['VAT_RATE']);
					$result['COMPARE_PRICE'] /= (1 + $priceData['VAT_RATE']);
					$result['BASE_PRICE'] /= (1 + $priceData['VAT_RATE']);
				}
			}
			else
			{
				if ($resultWithVat)
				{
					$result['PRICE'] *= (1 + $priceData['VAT_RATE']);
					$result['COMPARE_PRICE'] *= (1 + $priceData['VAT_RATE']);
					$result['BASE_PRICE'] *= (1 + $priceData['VAT_RATE']);
				}
			}

			$result['UNROUND_PRICE'] = $result['PRICE'];
			$result['UNROUND_BASE_PRICE'] = $result['BASE_PRICE'];

			$result['BASE_PRICE'] = Catalog\Product\Price::roundPrice(
				$priceData['CATALOG_GROUP_ID'],
				$result['BASE_PRICE'],
				$resultCurrency
			);
			$result['PRICE'] = Catalog\Product\Price::roundPrice(
				$priceData['CATALOG_GROUP_ID'],
				$result['PRICE'],
				$resultCurrency
			);
			if (empty($result['DISCOUNT_LIST']))
			{
				$result['BASE_PRICE'] = $result['PRICE'];
			}
			$result['COMPARE_PRICE'] = $result['PRICE'];

			if (empty($minimalPrice) || $minimalPrice['COMPARE_PRICE'] > $result['COMPARE_PRICE'])
			{
				$minimalPrice = $result;
			}

			unset($currentPrice, $result);
		}
		unset($priceData);
		unset($vat);

		$discountValue = ($minimalPrice['BASE_PRICE'] - $minimalPrice['PRICE']);

		$percent =($minimalPrice['BASE_PRICE'] > 0 && $discountValue > 0? roundEx((100*$discountValue)/$minimalPrice['BASE_PRICE'], 0): 0);

		if(Loader::includeModule('kit.price'))
		{
			$result = \KitPriceMain::GetOptimalPrice($productId,$qnt,$userGroups,$renewal,$prices,$siteId,$coupons);

			if(is_array($result))
			{
				$minimalPrice['BASE_PRICE'] = $result['PRICE']['PRICE'];
				$minimalPrice['PRICE'] = $result['PRICE']['PRICE'];
				$minimalPrice['RAW_PRICE']['PRICE'] = $result['PRICE']['PRICE'];
			}
		}

		if($_SESSION['KIT_REGIONS']['PRICE_VALUE'])
		{
			switch ($_SESSION['KIT_REGIONS']['PRICE_VALUE_TYPE'])
			{
				case 'PROCENT_UP':
					$minimalPrice['RAW_PRICE']['PRICE'] = $minimalPrice['RAW_PRICE']['PRICE'] + ($minimalPrice['RAW_PRICE']['PRICE'] / 100) * $_SESSION['KIT_REGIONS']['PRICE_VALUE'] ;
					$minimalPrice['BASE_PRICE'] = $minimalPrice['BASE_PRICE'] + ($minimalPrice['BASE_PRICE'] / 100) * $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					$minimalPrice['PRICE'] = $minimalPrice['BASE_PRICE'] - ($minimalPrice['BASE_PRICE'] / 100) * $percent;
					$minimalPrice['UNROUND_BASE_PRICE'] = $minimalPrice['UNROUND_BASE_PRICE'] + ($minimalPrice['UNROUND_BASE_PRICE'] / 100) * $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					$minimalPrice['UNROUND_PRICE'] = $minimalPrice['UNROUND_PRICE'] + ($minimalPrice['UNROUND_PRICE'] / 100) * $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					break;
				case 'PROCENT_DOWN':
					$minimalPrice['RAW_PRICE']['PRICE'] = $minimalPrice['RAW_PRICE']['PRICE'] - ($minimalPrice['RAW_PRICE']['PRICE'] / 100) * $_SESSION['KIT_REGIONS']['PRICE_VALUE'] ;
					$minimalPrice['BASE_PRICE'] = $minimalPrice['BASE_PRICE'] - ($minimalPrice['BASE_PRICE'] / 100) * $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					$minimalPrice['PRICE'] = $minimalPrice['BASE_PRICE'] - ($minimalPrice['BASE_PRICE'] / 100) * $percent;
					$minimalPrice['UNROUND_BASE_PRICE'] = $minimalPrice['UNROUND_BASE_PRICE'] - ($minimalPrice['UNROUND_BASE_PRICE'] / 100) * $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					$minimalPrice['UNROUND_PRICE'] = $minimalPrice['UNROUND_PRICE'] - ($minimalPrice['UNROUND_PRICE'] / 100) * $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					break;
				case 'FIX_UP':
					$minimalPrice['RAW_PRICE']['PRICE'] = $minimalPrice['RAW_PRICE']['PRICE'] + $_SESSION['KIT_REGIONS']['PRICE_VALUE'] ;
					$minimalPrice['BASE_PRICE'] = $minimalPrice['BASE_PRICE'] + $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					$minimalPrice['PRICE'] = $minimalPrice['BASE_PRICE'] - ($minimalPrice['BASE_PRICE'] / 100) * $percent;
					$minimalPrice['UNROUND_BASE_PRICE'] = $minimalPrice['UNROUND_BASE_PRICE'] + $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					$minimalPrice['UNROUND_PRICE'] = $minimalPrice['UNROUND_PRICE'] + $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					break;
				case 'FIX_DOWN':
					$minimalPrice['RAW_PRICE']['PRICE'] = $minimalPrice['RAW_PRICE']['PRICE'] - $_SESSION['KIT_REGIONS']['PRICE_VALUE'] ;
					$minimalPrice['BASE_PRICE'] = $minimalPrice['BASE_PRICE'] - $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					$minimalPrice['PRICE'] = $minimalPrice['BASE_PRICE'] - ($minimalPrice['BASE_PRICE'] / 100) * $percent;
					$minimalPrice['UNROUND_BASE_PRICE'] = $minimalPrice['UNROUND_BASE_PRICE'] - $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					$minimalPrice['UNROUND_PRICE'] = $minimalPrice['UNROUND_PRICE'] - $_SESSION['KIT_REGIONS']['PRICE_VALUE'];
					break;
			}
		}

		$arResult = array(
			'PRICE' => $minimalPrice['RAW_PRICE'],
			'RESULT_PRICE' => array(
				'PRICE_TYPE_ID' => $minimalPrice['RAW_PRICE']['CATALOG_GROUP_ID'],
				'BASE_PRICE' => $minimalPrice['BASE_PRICE'],
				'DISCOUNT_PRICE' => $minimalPrice['PRICE'],
				'CURRENCY' => $resultCurrency,
				'DISCOUNT' => $discountValue,
				'PERCENT' => $percent,
				'VAT_RATE' => $minimalPrice['RAW_PRICE']['VAT_RATE'],
				'VAT_INCLUDED' => ($resultWithVat ? 'Y' : 'N'),
				'UNROUND_BASE_PRICE' => $minimalPrice['UNROUND_BASE_PRICE'],
				'UNROUND_DISCOUNT_PRICE' => $minimalPrice['UNROUND_PRICE']
			),
			'DISCOUNT_PRICE' => $minimalPrice['PRICE'],
			'DISCOUNT' => array(),
			'DISCOUNT_LIST' => array(),
			'PRODUCT_ID' => $productId
		);
		if (!empty($minimalPrice['DISCOUNT_LIST']))
		{
			reset($minimalPrice['DISCOUNT_LIST']);
			$arResult['DISCOUNT'] = current($minimalPrice['DISCOUNT_LIST']);
			$arResult['DISCOUNT_LIST'] = $minimalPrice['DISCOUNT_LIST'];
		}
		unset($minimalPrice);
		return $arResult;
	}

	/**
	 * @return EventResult
	 */
	public static function onSaleDeliveryRestrictionsClassNamesBuildListHandler()
	{
		return new EventResult(
			EventResult::SUCCESS,
			array(
				'\\Kit\\Regions\\Sale\\Restriction\\Delivery' => '/bitrix/modules/kit.regions/lib/sale/restriction/delivery.php',
			)
		);
	}

	/**
	 * @return EventResult
	 */
	public static function onSalePaySystemRestrictionsClassNamesBuildListHandler()
	{
		return new EventResult(
			EventResult::SUCCESS,
			array(
				'\\Kit\\Regions\\Sale\\Restriction\\Paysystem' => '/bitrix/modules/kit.regions/lib/sale/restriction/paysystem.php',
			)
		);
	}

	/**
	 * @param Event $event
	 */
	public static function OnSaleOrderBeforeSavedHandler(Event $event)
	{
		$entity = $event->getParameter( 'ENTITY' );
		if( $entity instanceof \Bitrix\Sale\Order )
		{
			$order = new Order($entity);
			if($order->isNeedAddOrderProperty())
			{
				$prop = $order->getProperty($entity);
				if($prop instanceof PropertyValue)
				{
					$propertyCollection = $entity->getPropertyCollection();
					try
					{
						$propertyCollection->addItem($prop);
					}
					catch (ArgumentTypeException $e)
					{
					}
				}
			}
			if($order->isNeedAddOrderManager())
			{
				$entity->setField('RESPONSIBLE_ID',$_SESSION['KIT_REGIONS']['MANAGER']);
			}
		}
	}

	public static function OnCondSaleControlBuildListHandler() {
        return Discount::getControlDescr();
    }
}