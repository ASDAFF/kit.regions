<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 22-Jan-18
 * Time: 4:25 PM
 */
namespace Kit\Regions\Config;
/**
 * Class Tab
 * @package Kit\Regions\Config
 */
class Tab
{
	protected $code;
	protected $groups;
	public function __construct($code)
	{
		$this->groups = new \Kit\Regions\Collection();
		$this->setCode($code);
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
	public function getGroups()
	{
		return $this->groups;
	}
}