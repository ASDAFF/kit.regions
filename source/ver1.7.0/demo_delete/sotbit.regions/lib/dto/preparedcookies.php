<?php

namespace Sotbit\Regions\DTO;


class PreparedCookies
{
    /** @var string $key */
    public $key;

    /** @var string|int|float $value */
    public $value;

    /** @param mixed $value */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}