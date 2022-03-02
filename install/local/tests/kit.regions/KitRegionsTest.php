<?php
/**
 * Created by PhpStorm.
 * Date: 02-Feb-18
 * Time: 10:03 AM
 */

use PHPUnit\Framework\TestCase;
use Bitrix\Main\Loader;

class KitRegionsTest extends TestCase
{

	public function testModuleInstalled()
	{
		$this->assertTrue(Loader::includeModule("kit.regions"));
	}
	/**
	 * @depends testModuleInstalled
	 */
	public function testIsDemoEnd()
	{
		$isDemoEnd = KitRegions::isDemoEnd();

		$this->assertFalse($isDemoEnd);
	}
	/**
	 * @depends testModuleInstalled
	 */
	public function testGetDemo()
	{
		$demo = KitRegions::getDemo();
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
		$sites = KitRegions::getSites();
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
			$return = KitRegions::getMenuParent($parent);
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
		$return = KitRegions::genCodeVariable($code);
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
