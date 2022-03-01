<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 23-Jan-18
 * Time: 9:59 AM
 */

namespace Sotbit\Regions\Config\Widgets;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

/**
 * Class Variables
 * @package Sotbit\Regions\Config\Widgets
 * @author Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class Variables extends Text
{
	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function setValues($values = array())
	{
		$settings = $this->getSettings();

		$tags = \SotbitRegions::getTags(array($settings['SITE_ID']));
		foreach($tags as $tag)
		{
			$this->setValue($tag['CODE'], $tag['NAME']);
		}
	}

	/**
	 * @param $id
	 * @param $value
	 */
	public function setValue($id,$value)
	{
		$this->values[$id] = $value;
	}

	/**
	 *
	 */
	public function show()
	{
		$values = $this->getValues();
		if($values)
		{
			echo '<tr><td style="text-align: center;" width="25%"><b>';
			echo Loc::getMessage(\SotbitRegions::moduleId.'_WIDGET_'.$this->getCode().'_NAME');
			echo '</b></td><td style="text-align: center;" width="25%"><b>';
			echo Loc::getMessage(\SotbitRegions::moduleId.'_WIDGET_'.$this->getCode().'_CODE');
			echo '</b></td><td style="text-align: center;" width="50%"><b>';
			echo Loc::getMessage(\SotbitRegions::moduleId.'_WIDGET_'.$this->getCode().'_VARIABLE');
			echo '</b></td></tr>';
		}
		foreach($values as $code => $name)
		{
			echo '
				<tr><td style="text-align: center;" width="25%">',$name,'</td>
				<td style="text-align: center;" width="25%">',\SotbitRegions::genCodeVariable($code),'</td>
				<td style="text-align: center;" width="50%">$_SESSION["SOTBIT_REGIONS"]["',$code,'"]</td></tr>';
		}
	}

}