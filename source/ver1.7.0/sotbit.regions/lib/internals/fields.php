<?php
namespace Sotbit\Regions\Internals;

use Bitrix\Main;
use	Bitrix\Main\Localization\Loc;
use	Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
Loc::loadMessages(__FILE__);

/**
 * Class FieldsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ID_REGION int optional
 * <li> CODE string(255) mandatory
 * <li> VALUE string optional
 * </ul>
 *
 * @package Sotbit\Regions
 **/

class FieldsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sotbit_regions_fields';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('FIELDS_ENTITY_ID_FIELD'),
			),
			'ID_REGION' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FIELDS_ENTITY_ID_REGION_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('FIELDS_ENTITY_CODE_FIELD'),
			),
			'VALUE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('FIELDS_ENTITY_VALUE_FIELD'),
			),
			new ExpressionField(
				'REGION',
				'Sotbit\Regions\Internals\RegionsTable',
				array('=this.ID_REGION' => 'ref.ID')
            )
		);
	}
	/**
	 * Returns validators for CODE field.
	 *
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @return array
	 */
	public static function validateCode()
	{
		return array(
			new LengthValidator(null, 255),
		);
	}

	public static function OnAdd(Event $event)
	{
		FieldsTable::getEntity()->cleanCache();
	}

	public static function OnUpdate(Event $event)
	{
		FieldsTable::getEntity()->cleanCache();
	}

	public static function OnDelete(Event $event)
	{
		FieldsTable::getEntity()->cleanCache();
	}
}