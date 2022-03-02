<?php

namespace Kit\Regions\System\Traits;

use Kit\Regions\Internals;
use Kit\Regions\DTO;

trait getAllRegionTrait
{
    private function getAllRegion(array $actions): DTO\ResponseData
    {
        $result = new DTO\ResponseData();

        $region = Internals\RegionsTable::query()
            ->addSelect('ID')
            ->addSelect('NAME')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->fetchAll();

        $result->actions = $actions;
        $result->allRegions = array_column($region, 'NAME', 'ID');

        return $result;
    }
}