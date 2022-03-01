<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 23-Jan-18
 * Time: 9:59 AM
 */

namespace Sotbit\Regions\Config\Widgets;

use Bitrix\Main\Loader;

class IblockId extends Select
{
	public function setValues()
	{
		if(Loader::includeModule('iblock'))
		{
			$iblockType = \Sotbit\Regions\Config\Option::get('IBLOCK_TYPE');
			$filter = array('ACTIVE' => 'Y');
			if($iblockType)
			{
				$filter['IBLOCK_TYPE_ID'] = $iblockType;
			}
			$rs = \Bitrix\Iblock\IblockTable::getList(
				array(
					'select' => array(
						'ID',
						'NAME'
					),
					'filter' => $filter
				)
			);
			while ($iblockId = $rs->fetch())
			{
				$this->setValue(
					$iblockId['ID'],
					$iblockId['NAME']
				);
			}
		}
	}
}