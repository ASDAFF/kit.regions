<?php

class SotbitRegionsChooseComponent_new extends \CBitrixComponent
{
    private static $counter = 0;

    public function __construct($component = null)
    {
        $component++;
        parent::__construct($component);
    }

    public function executeComponent()
    {
        if (static::$counter > 1) {
            return;
        }

        $this->includeComponentTemplate();
    }
}