<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


$this->setFrameMode(true);
$frame = $this->createFrame()->begin("");
?>

<div class="select-city-wrap" id="regions_choose_component">
    <div class="select-city__block">
        <span class="select-city__block__text"><?= Loc::getMessage(KitRegions::moduleId . '_YOUR_CITY') ?>: </span>
        <span class="select-city__block__text-city"
            data-entity="select-city__block__text-city"
        >______</span>
    </div>
    <div class="select-city__dropdown-wrap"
        id="regions_choose_component_dropdown"
        style="display:none;"
    >
        <div class="select-city__dropdown">
            <div class="select-city__dropdown__title-wrap">
                <span class="select-city__dropdown__title"
                    data-entity="select-city__dropdown__title"
                >
                    <?= Loc::getMessage(KitRegions::moduleId . '_YOUR_CITY') . ' ###?' ?>
                </span>
            </div>
            <div class="select-city__dropdown__choose-wrap">
                <span class="select-city__dropdown__choose__yes select-city__dropdown__choose"
                    data-entity="select-city__dropdown__choose__yes"
                >
                    <?= Loc::getMessage(KitRegions::moduleId . '_YES') ?>
                </span>
                <span class="select-city__dropdown__choose__no select-city__dropdown__choose"
                    data-entity="select-city__dropdown__choose__no"
                >
                    <?= Loc::getMessage(KitRegions::moduleId . '_NO') ?>
                </span>
            </div>
        </div>
    </div>
</div>
<div id="regon_choose_select-city__modal" style="display: none;" class="select-city__modal">
    <div class="select-city__modal-wrap">
        <div class="select-city__close" data-entity="select-city__close"></div>

        <div class="select-city__tabs_wrapper">
            <ul class="select-city__tabs" data-entity="kit-regions-tabs">

            </ul>
        </div>

        <div class="select-city__modal__title-wrap">
            <p class="select-city__modal__title">

            </p>
        </div>
        <input class="select-city__input"
                data-entity="select-city__modal__submit__input"
                placeholder="<?= Loc::getMessage(KitRegions::moduleId . '_WRITE_SITY') ?>">

            <div class="select-city__wrapper__input">
                <div class="select-city__input__comment select-city__under_input" data-entity="select-city__input__example">
                    <?=Loc::getMessage('kit.regions_EXAMPLE')?>
                </div>
            </div>
            <div class="select-city__tab_content">
                <div class="select-city__list_wrapper">
                    <div class="select-city__tab_name_content__big_city"
                        style="display: none;"
                        data-entity="select-city__tab_name_content__big_city"
                    ><?= Loc::getMessage(KitRegions::moduleId . '_BIG_CITIES') ?></div>
                    <div class="select-city__list_wrapper_favorites"
                        data-entity="select-city__list_wrapper_favorites"
                    >
                        <div class="select-city__list" data-entity="select-city__list"></div>
                    </div>
                    <div class="select-city__tab_name_content__village"><?= Loc::getMessage(KitRegions::moduleId . '_CITIES') ?></div>
                    <div class="select-city__list_wrapper_cities" data-entity="select-city__list_wrapper_cities"></div>
                </div>
            </div>
    </div>

</div>

<script>
    $componentRegionsChoose = new RegionsChoose();
</script>
<? $frame->end();?>
