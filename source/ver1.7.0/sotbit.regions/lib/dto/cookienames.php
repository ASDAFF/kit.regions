<?php

namespace Sotbit\Regions\DTO;


/**
 * @property string $region
 * @property string $location
 */

class CookieNames
{
    public $region;
    public $location;

    public function __construct(string $region, string $location)
    {
        $this->region = $region;
        $this->location = $location;
    }
}