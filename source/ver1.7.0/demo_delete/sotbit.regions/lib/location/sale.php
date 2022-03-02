<?
namespace Sotbit\Regions\Location;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
/**
 * Class Sale
 * @package Sotbit\Regions\Location
 * @author Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class Sale
{
	/**
	 * @var int|null
	 */
	public static $propertyId = null;
	/**
	 * @var int|null
	 */
	public static $Id = null;

	/**
	 * Sale constructor.
	 * @param $personTypeId
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($personTypeId)
	{
		if(is_null(self::$propertyId))
		{
			self::$propertyId = $this->setPropertyId($personTypeId);
		}
		if(is_null(sale::$Id))
		{
			self::$Id = $this->setId($personTypeId);
		}
	}

	/**
	 * @param int $personTypeId
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function setPropertyId($personTypeId = 0)
	{
		$propertyId = 0;
		if(!$personTypeId)
		{
			return $propertyId;
		}

		if(
			isset($_SESSION['SOTBIT_REGIONS']['SALE_LOCATION'][$personTypeId]) &&
			is_array($_SESSION['SOTBIT_REGIONS']['SALE_LOCATION'][$personTypeId]) > 0 &&
			key($_SESSION['SOTBIT_REGIONS']['SALE_LOCATION'][$personTypeId]) > 0
		)
		{
			$propertyId = key($_SESSION['SOTBIT_REGIONS']['SALE_LOCATION'][$personTypeId]);
		}
		else
		{
			$property = \Bitrix\Sale\Internals\OrderPropsTable::getList(
				array(
					'filter' => array(
						'ACTIVE' => 'Y',
                        'TYPE' => 'LOCATION',
						'PERSON_TYPE_ID' => $personTypeId,
					),
					'select' => array(
						'ID'
					),
					'limit' => 1,
					'cache' => array(
						'ttl' => 36000000,
					)
				)
			)->fetch();
			if($property['ID'] > 0)
			{
				$propertyId = $property['ID'];
				$_SESSION['SOTBIT_REGIONS']['SALE_LOCATION'][$personTypeId] = array($property['ID'] => 0);
			}
		}
		return $propertyId;
	}

	/**
	 * @param int $personTypeId
	 * @return int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function setId($personTypeId = 0)
	{
		try
		{
			if(is_null(self::$propertyId))
			{
				throw new SystemException("Not find sale location property Id");
			}
			$return = 0;
			if($_SESSION['SOTBIT_REGIONS']['SALE_LOCATION'][$personTypeId][self::$propertyId] > 0)
			{
				$return = $_SESSION['SOTBIT_REGIONS']['SALE_LOCATION'][$personTypeId][self::$propertyId];
			}
			else
			{
				$domain = new Domain();
				if(is_null($domain->getProp('NAME')))
				{
					throw new SystemException("Empty city name");
				}
				if(!Loader::includeModule('sale'))
				{
					throw new SystemException("Module sale not install");
				}

				$saleLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
					'filter' => array('=NAME.NAME' => $domain->getProp('NAME'), '=NAME.LANGUAGE_ID' => LANGUAGE_ID),
					'select' => array('CODE'),
					'cache' => array(
						'ttl' => 36000000,
					)
				))->fetch();
				if($saleLocation['CODE'] > 0)
				{
					$return = $saleLocation['CODE'];
				}
			}
			return $return;
		}
		catch (SystemException $exception)
		{
			echo $exception->getMessage();
		}
	}

	/**
	 * @return int|null
	 */
	public function getPropertyId()
	{
		return self::$propertyId;
	}

	/**
	 * @return int|null
	 */
	public function getId()
	{
		return self::$Id;
	}
}