<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 23-Jan-18
 * Time: 9:59 AM
 */

namespace Sotbit\Regions\Config\Widgets;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class HL extends Select
{
	public function setValues()
	{
		if(Loader::includeModule('highloadblock'))
		{
			$rs = HighloadBlockTable::getList(
				array(
					'select' => array(
						'ID',
						'NAME'
					),
				)
			);
			while ($HL = $rs->fetch())
			{
				$this->setValue(
					$HL['ID'],
					$HL['NAME']
				);
			}
		}
	}
	public function show()
	{
		echo SelectBoxFromArray(
			$this->getCode(),
			$this->values,
			$this->getCurrentValue(),
			'',
			($this->settings['refresh'] == 'Y')?'onchange="submit();"':'');

		if($this->getCurrentValue() > 0)
		{
			echo '<br><a href="/bitrix/admin/highloadblock_rows_list.php?ENTITY_ID='.$this->getCurrentValue().'&lang='
				.LANGUAGE_ID.'" target="_blank">';
			echo Loc::getMessage(\SotbitRegions::moduleId.'_CURRENT_HL');
			echo '</a>';
		}
	}
}