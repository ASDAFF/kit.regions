<?php

namespace Sotbit\Regions\DTO;

use Bitrix\Main\Type\Contract\Arrayable;

/**
 * @property null|string[] $actions
 * @property null|string $currentRegionName
 * @property null|string[int] $allRegions
 * @property null|int $currentRegionId
 * @property null|string $currentRegionCode
 * @property null|int $sotbitRegionsId
 * @property null|array $locationTemplateData
 */

class ResponseData implements Arrayable
{
    public $actions;
    public $currentRegionName;
    public $allRegions;
    public $currentRegionId;
    public $currentRegionCode;
    public $sotbitRegionsId;
    public $country;
    public $locationTemplateData;

    public function toArray(): array
    {
        return (array)$this;
    }
}