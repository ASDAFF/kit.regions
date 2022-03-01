<?php
namespace Sotbit\Regions\Internals;

use Bitrix\Main\GroupTable;
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);


class RegionsTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sotbit_regions';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_ID_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_CODE_FIELD'),
			),
            'DEFAULT_DOMAIN' => array(
                'data_type' => 'string',
                'title' => Loc::getMessage(\SotbitRegions::moduleId.'_DEFAULT_DOMAIN'),
            ),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_NAME_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_SORT_FIELD'),
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_SITE_ID_FIELD'),
				'serialized' => true,
			),
			'PRICE_CODE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_PRICE_CODE_FIELD'),
				'serialized' => true,
			),
			'STORE' => array(
				'data_type' => 'text',
				'serialized' => true,
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_STORE_FIELD'),
			),
			'COUNTER' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_COUNTER_FIELD'),
			),
			'MAP_YANDEX' => array(
				'data_type' => 'text',
				'serialized' => true,
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_MAP_YANDEX_FIELD'),
			),
			'MAP_GOOGLE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_MAP_GOOGLE_FIELD'),
				'serialized' => true,
			),
			'MANAGER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_MANAGER_FIELD'),
			),
			'PRICE_VALUE_TYPE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_PRICE_VALUE_TYPE_FIELD'),
			),
			'PRICE_VALUE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage(\SotbitRegions::moduleId.'_REGIONS_ENTITY_PRICE_VALUE_FIELD'),
			),
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
			new Entity\Validator\Length(null, 100),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * @param Entity\Event $event
	 */
	public static function OnAdd(Entity\Event $event)
	{
		RegionsTable::getEntity()->cleanCache();
		GroupTable::getEntity()->cleanCache();
	}

	/**
	 * @param Entity\Event $event
	 */
	public static function OnUpdate(Entity\Event $event)
	{
		RegionsTable::getEntity()->cleanCache();
        GroupTable::getEntity()->cleanCache();
	}

	/**
	 * @param Entity\Event $event
	 */
	public static function OnDelete(Entity\Event $event)
	{
		RegionsTable::getEntity()->cleanCache();
        GroupTable::getEntity()->cleanCache();
	}
}
?>