<?php

namespace Sotbit\Regions\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Event;

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

    public static function OnAdd(Event $event)
    {
        LocationsTable::getEntity()->cleanCache();
    }

    public static function OnUpdate(Event $event)
    {
        LocationsTable::getEntity()->cleanCache();
    }

    public static function OnDelete(Event $event)
    {
        LocationsTable::getEntity()->cleanCache();
    }
}