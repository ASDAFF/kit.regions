<?php
namespace Sotbit\Regions\Internals;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
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

class FieldsTable extends Main\Entity\DataManager
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
			new Main\Entity\ReferenceField(
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
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * @param Main\Entity\Event $event
	 */
	public static function OnAdd(Main\Entity\Event $event)
	{
		FieldsTable::getEntity()->cleanCache();
	}

	/**
	 * @param Main\Entity\Event $event
	 */
	public static function OnUpdate(Main\Entity\Event $event)
	{
		FieldsTable::getEntity()->cleanCache();
	}

	/**
	 * @param Main\Entity\Event $event
	 */
	public static function OnDelete(Main\Entity\Event $event)
	{
		FieldsTable::getEntity()->cleanCache();
	}
}