<?php

namespace Sotbit\Regions\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

class LocationsTable extends DataManager
{
    public static function getTableName()
    {
        return 'sotbit_regions_locations';
    }

    public static function getMap()
    {
        return [
            (new IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            )),
            (new IntegerField(
                'REGION_ID',
                [
                    'required'  => true
                ]
            )),
            (new IntegerField(
                'LOCATION_ID',
                [
                    'required'  => true
                ]
            )),
            (new Reference(
                'REGION',
                RegionsTable::class,
                Join::on('this.REGION_ID', 'ref.ID')
            ))->configureJoinType('inner'),
        ];
    }

    /**
     * @param Main\Entity\Event $event
     */
    public static function OnAdd(Main\Entity\Event $event)
    {
        LocationsTable::getEntity()->cleanCache();
    }

    /**
     * @param Main\Entity\Event $event
     */
    public static function OnUpdate(Main\Entity\Event $event)
    {
        LocationsTable::getEntity()->cleanCache();
    }

    /**
     * @param Main\Entity\Event $event
     */
    public static function OnDelete(Main\Entity\Event $event)
    {
        LocationsTable::getEntity()->cleanCache();
    }
}