<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 22-Jan-18
 * Time: 2:15 PM
 */

namespace Kit\Regions\Config\Widgets;

use Bitrix\Main\Localization\Loc;
use Kit\Regions\Config\Widget;

class AnyElement extends Widget
{
	public function show()
	{
		require($this->getSetting('path'));
	}
}
?>
