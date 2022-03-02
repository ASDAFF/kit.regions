<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 23-Jan-18
 * Time: 9:03 AM
 */

namespace Kit\Regions\Config;

use Bitrix\Main\SystemException;
use Kit\Regions\Internals\OptionsTable;
use Bitrix\Main\Application;

/**
 * Class Main
 * @package Kit\Regions\Config
 */
class Option
{
	/**
	 * @var null|array
	 */
	public static $options = null;

	/**
	 * @param string $code
	 * @param string $site
	 * @return mixed
	 */
	public static function get(
		$code = '',
		$site = ''
	)
	{
		try
		{
			if(!$code)
			{
				throw new SystemException("Empty code");
			}
			if(is_null(self::$options))
			{
				self::setOptions($site);
			}
			return self::$options[$code];
		}
		catch (SystemException $exception)
		{
			echo $exception->getMessage();
		}
	}

	/**
	 * @param $site
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function setOptions($site = '')
	{
		if(is_null(self::$options))
		{
			$filter = array('SITE_ID' => $site);
			$rs = OptionsTable::getList(
				array(
					'filter' => $filter,
					'select' => array(
						'CODE',
						'VALUE'
					),
					'cache' => array(
						'ttl' => 36000000,
					)
				)
			);
			while ($option = $rs->fetch())
			{
				if(self::isJson($option['VALUE']))
				{
					self::$options[$option['CODE']] = json_decode($option['VALUE']);
				}
				else
				{
					self::$options[$option['CODE']] = $option['VALUE'];
				}
			}
		}
	}

	/**
	 * @param $str
	 * @return bool
	 */
	public function isJson($str)
	{
		return (is_string($str) && mb_substr($str, 0, 1, LANG_CHARSET) == '{' && mb_substr($str, -1, 1, LANG_CHARSET) == '}') ?
            true : false;
	}

	/**
	 * @param $code
	 * @param $value
	 * @param $site
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Exception
	 */
	public static function set(
		$code,
		$value,
		$site
	)
	{
		if(is_array($value))
		{
			$value = json_encode($value);
		}
		$option = OptionsTable::getList(
			array(
				'filter' => array(
					'CODE' => $code,
					'SITE_ID' => $site
				),
				'select' => array('CODE'),
				'limit' => 1
			)
		)->fetch();

		if($option['CODE'])
		{
			$con = Application::getConnection();
			$sqlHelper = $con->getSqlHelper();
			$strSqlWhere = sprintf(
				"SITE_ID %s AND CODE = '%s'",
				($site == "") ? "IS NULL" : "= '" . $sqlHelper->forSql($site, 2) . "'",
				$sqlHelper->forSql($code)
			);
			$con->queryExecute(
				"UPDATE " . OptionsTable::getTableName() . " SET " .
				"	VALUE = '" . $sqlHelper->forSql($value) . "' " .
				"WHERE " . $strSqlWhere
			);

			OptionsTable::getEntity()->cleanCache();
		}
		else
		{
			OptionsTable::add(
				array(
					'CODE' => $code,
					'VALUE' => $value,
					'SITE_ID' => $site
				)
			);
		}
	}
}