<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


$this->createFrame()->begin("_____");
?>

<div class="select-city-wrap" id="regions_choose_component">
    <div class="select-city__block">
        <span class="select-city__block__text-city"
            data-entity="open_region"
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
        <div class="select_city-modal-title">
            <div class="modal_title-text-wrapper">
                <span class="title"><?= Loc::getMessage(KitRegions::moduleId . '_SELECT_REGION') ?></span>
                <span class="current_region-title"><?= Loc::getMessage(KitRegions::moduleId . '_YOUR_REGION') ?></span>
                <span class="current_region" data-entity="select-city__name"></span>
            </div>
            <div class="select-city__close" data-entity="select-city__close"></div>
        </div>
        <div class="scroll-container ps ps--active-y">
            <div class="city_modal-content-wrapper">
                <div class="input-block-wrapper">
                    <div class="tabs_wrapper" style="height: 60px;">
                        <div class="select-city__tabs_wrapper">
                            <ul class="select-city__tabs" data-entity="kit-regions-tabs"></ul>
                        </div>
                    </div>
                    <input class="select-city__input"
                            data-entity="select-city__modal__submit__input"
                            placeholder="<?= Loc::getMessage(KitRegions::moduleId . '_WRITE_SITY') ?>">
                    <div class="input-example-wrapper">
                        <div class="select-city__wrapper__input">
                            <div class="select-city__input__comment select-city__under_input" data-entity="select-city__input__example">
                                <?=Loc::getMessage('kit.regions_EXAMPLE')?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="select-city__tab_content active">
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
    </div>

</div>
<div  id="regon_choose_modal__overlay" style="display: none;" class="modal__overlay"></div>

<script>
    $componentRegionsChoose = new RegionsChoose();
</script>

