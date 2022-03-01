<?php
namespace Sotbit\Regions\Internals;

use	Bitrix\Main\Localization\Loc;
use	Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class OptionsTable
 *
 * Fields:
 * <ul>
 * <li> NAME string(50) mandatory
 * <li> VALUE string optional
 * <li> SITE_ID string(2) optional
 * </ul>
 *
 * @package Bitrix\Regions
 **/

class OptionsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sotbit_regions_options';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CODE' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_OPTIONS_ENTITY_CODE_FIELD'),
			),
			'VALUE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_OPTIONS_ENTITY_VALUE_FIELD'),
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateSiteId'),
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_OPTIONS_ENTITY_SITE_ID_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return array(
			new LengthValidator(null, 50),
		);
	}
	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId()
	{
		return array(
			new LengthValidator(null, 2),
		);
	}
	public static function OnAdd(Event $event)
	{
		\Sotbit\Regions\Internals\OptionsTable::getEntity()->cleanCache();
	}
	public static function OnUpdate(Event $event)
	{
		\Sotbit\Regions\Internals\OptionsTable::getEntity()->cleanCache();
	}
	public static function OnDelete(Event $event)
	{
		\Sotbit\Regions\Internals\OptionsTable::getEntity()->cleanCache();
	}
}
?>