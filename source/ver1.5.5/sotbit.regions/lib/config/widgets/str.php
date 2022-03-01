<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 22-Jan-18
 * Time: 2:15 PM
 */

namespace Sotbit\Regions\Config\Widgets;


use Sotbit\Regions\Config\Widget;

class Str extends Widget
{
	public function show()
	{
		echo '<input type="text" name="'.htmlspecialcharsbx($this->getCode()).'" value="'.htmlspecialcharsbx
            ($this->getCurrentValue()).'" />';
	}
}
?>