<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 22-Jan-18
 * Time: 4:25 PM
 */
namespace Sotbit\Regions\Config;
/**
 * Class Tab
 * @package Sotbit\Regions\Config
 * @author Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class Tab
{
	protected $code;
	protected $groups;
	public function __construct($code)
	{
		$this->groups = new \Sotbit\Regions\Collection();
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
	 * @return \Sotbit\Regions\Collection
	 */
	public function getGroups()
	{
		return $this->groups;
	}
}