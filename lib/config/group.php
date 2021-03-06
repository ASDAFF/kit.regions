<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 22-Jan-18
 * Time: 4:26 PM
 */

namespace Kit\Regions\Config;
/**
 * Class Group
 * @package Kit\Regions\Config
 */
class Group
{
	protected $widgets;
	protected $code;
	protected $settings = array('COLSPAN' => 2);
	public function __construct($code, $settings = array())
	{
		$this->widgets = new \Kit\Regions\Collection();
		$this->setCode($code);
		$this->settings = array_merge($this->settings,$settings);
	}

	/**
	 * @return mixed
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param mixed $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return \Kit\Regions\Collection
	 */
	public function getWidgets()
	{
		return $this->widgets;
	}

	/**
	 * @return mixed
	 */
	public function getSetting($code)
	{
		return $this->settings[$code];
	}

}