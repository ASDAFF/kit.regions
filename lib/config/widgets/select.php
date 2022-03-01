<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 22-Jan-18
 * Time: 2:15 PM
 */

namespace Kit\Regions\Config\Widgets;


use Kit\Regions\Config\Widget;

class Select extends Widget
{
	public function setValues($values = array())
	{
		foreach($values as $key => $value)
		{
			$this->setValue($key,$value);
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
	}
}
?>