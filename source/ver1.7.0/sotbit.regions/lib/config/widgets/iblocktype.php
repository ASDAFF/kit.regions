<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 23-Jan-18
 * Time: 9:59 AM
 */

namespace Sotbit\Regions\Config\Widgets;

use Bitrix\Main\Loader;

class IblockType extends Select
{
	public function setValues()
	{
		if(Loader::includeModule('iblock'))
		{
			$rs = \Bitrix\Iblock\TypeTable::getList(
				array(
					'select' => array(
						'ID',
						'LANG_MESSAGE.NAME'
					),
					'filter' => array(
						'LANG_MESSAGE.LANGUAGE_ID' => LANGUAGE_ID
					)
				)
			);
			while ($iblockType = $rs->fetch())
			{
				$this->setValue(
					$iblockType['ID'],
					$iblockType['IBLOCK_TYPE_LANG_MESSAGE_NAME']
				);
			}
		}
	}
}