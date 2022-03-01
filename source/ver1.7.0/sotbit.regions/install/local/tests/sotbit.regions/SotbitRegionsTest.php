<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 02-Feb-18
 * Time: 10:03 AM
 */

use PHPUnit\Framework\TestCase;
use Bitrix\Main\Loader;

class SotbitRegionsTest extends TestCase
{

	public function testModuleInstalled()
	{
		$this->assertTrue(Loader::includeModule("sotbit.regions"));
	}
	/**
	 * @depends testModuleInstalled
	 */
	public function testIsDemoEnd()
	{
		$isDemoEnd = SotbitRegions::isDemoEnd();

		$this->assertFalse($isDemoEnd);
	}
	/**
	 * @depends testModuleInstalled
	 */
	public function testGetDemo()
	{
		$demo = SotbitRegions::getDemo();
		if(in_array($demo,array(0,1,2,3)))
		{
			$this->assertTrue(true);
		}
		else
		{
			$this->assertTrue(false);
		}
	}
	/**
	 * @depends testModuleInstalled
	 */
	public function testGetSites()
	{
		$sites = SotbitRegions::getSites();
		if(is_array($sites) && count($sites) > 0 )
		{
			$this->assertTrue(true);
		}
		else
		{
			$this->assertTrue(false);
		}
	}
	/**
	 * @depends testModuleInstalled
	 * @dataProvider dataProvider
	 */
	public function testGetMenuParent($parent)
	{

		try
		{
			$return = SotbitRegions::getMenuParent($parent);
			if(is_string($return) && mb_strlen($return) > 0)
			{
				$this->assertTrue(true);
			}
			else
			{
				$this->fail("Expected Exception has not been raised.");
			}
		}
		catch(Exception $ex)
		{
			$this->assertEquals($ex->getMessage(), "Exception message");
		}
	}
	/**
	 * @depends testModuleInstalled
	 * @dataProvider dataProvider
	 */
	public function testGenCodeVariable($code)
	{
		$return = SotbitRegions::genCodeVariable($code);
		var_dump($return);
		if(is_string($return) && mb_strlen($return) > 0)
		{
			$this->assertTrue(true);
		}
	}

	/**
	 * @return array
	 */
	public function dataProvider() {
		return array(
			array(1),
			array(0),
			array(NULL),
			array('test'),
			array(''),
			array(true),
			array(false),
			array(array()),
		);
	}
}
