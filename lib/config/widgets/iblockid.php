<?php
/**
 * Created by PhpStorm.
 * Date: 23-Jan-18
 * Time: 9:59 AM
 */

namespace Kit\Regions\Config\Widgets;

use Bitrix\Main\Loader;

class IblockId extends Select
{
	public function setValues()
	{
		if(Loader::includeModule('iblock'))
		{
			$iblockType = \Kit\Regions\Config\Option::get('IBLOCK_TYPE');
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