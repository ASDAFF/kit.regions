<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 23-Jan-18
 * Time: 9:59 AM
 */

namespace Kit\Regions\Config\Widgets;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

/**
 * Class Variables
 * @package Kit\Regions\Config\Widgets
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

		$tags = \KitRegions::getTags(array($settings['SITE_ID']));
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
			echo Loc::getMessage(\KitRegions::moduleId.'_WIDGET_'.$this->getCode().'_NAME');
			echo '</b></td><td style="text-align: center;" width="25%"><b>';
			echo Loc::getMessage(\KitRegions::moduleId.'_WIDGET_'.$this->getCode().'_CODE');
			echo '</b></td><td style="text-align: center;" width="50%"><b>';
			echo Loc::getMessage(\KitRegions::moduleId.'_WIDGET_'.$this->getCode().'_VARIABLE');
			echo '</b></td></tr>';
		}
		foreach($values as $code => $name)
		{
			echo '
				<tr><td style="text-align: center;" width="25%">',$name,'</td>
				<td style="text-align: center;" width="25%">',\KitRegions::genCodeVariable($code),'</td>
				<td style="text-align: center;" width="50%">$_SESSION["KIT_REGIONS"]["',$code,'"]</td></tr>';
		}
	}

}