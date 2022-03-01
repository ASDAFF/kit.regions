<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 22-Jan-18
 * Time: 2:15 PM
 */

namespace Kit\Regions\Config\Widgets;


use Kit\Regions\Config\Widget;

class Str extends Widget
{
	public function show()
	{
		echo '<input type="text" name="'.htmlspecialcharsbx($this->getCode()).'" value="'.htmlspecialcharsbx
            ($this->getCurrentValue()).'" />';
	}
}
?>