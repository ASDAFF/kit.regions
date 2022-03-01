<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 23-Jan-18
 * Time: 9:59 AM
 */

namespace Sotbit\Regions\Config\Widgets;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Catalog\StoreTable;

class Store extends Select
{
	public function setValues()
	{
		if(Loader::includeModule('catalog'))
		{
			$rs = StoreTable::getList();
		}
		$iblockId = \Sotbit\Regions\Config\Option::get('IBLOCK_ID');
		$filter = array(
			'ACTIVE' => 'Y',
			'PROPERTY_TYPE' => 'S',
			'MULTIPLE' => 'Y'
		);
		if(Loader::includeModule('iblock') && $iblockId > 0)
		{
			$filter['IBLOCK_ID'] = $iblockId;
		}
		$rs = PropertyTable::getList(
			array(
				'select' => array(
					'ID',
					'NAME'
				),
				'filter' => $filter
			)
		);
		while($iblockId = $rs->fetch())
		{
			$this->setValue(
				$iblockId['ID'],
				$iblockId['NAME']
			);
		}
	}
}