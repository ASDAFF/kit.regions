<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 22-Jan-18
 * Time: 2:15 PM
 */

namespace Kit\Regions\Config\Widgets;


use Kit\Regions\Config\Widget;

class CheckBox extends Widget
{
	public function show()
	{
		echo '
		<input type="hidden" name="' . $this->getCode() . '" value="N" />
		<input 
				type="checkbox" 
				name="' . $this->getCode() . '" 
				id="' . $this->getCode() . '" 
				value="Y"
				' . ($this->getCurrentValue() == 'Y' ? ' checked' : '') . ' />';
	}
}

?>