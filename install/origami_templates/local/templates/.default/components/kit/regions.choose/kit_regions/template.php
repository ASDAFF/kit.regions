<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$this->createFrame()->begin("_____");
?>

<div class="select-city-wrap" id="regions_choose_component">
    <div class="select-city__block__text-city origami_icons_button"
        data-entity="open_region"
        >______</div>

    <div class="select-city__dropdown-wrap" id="regions_choose_component_dropdown" style="display: none;">
        <div class="select-city__dropdown">
            <div class="select-city__dropdown__title-wrap">
            <span class="select-city__dropdown__title" data-entity="select-city__dropdown__title">
                <?= Loc::getMessage(KitRegions::moduleId . '_YOUR_CITY') . ' ###?' ?>
            </span>
            </div>
            <div class="select-city__dropdown__choose-wrap">
                <span class="select-city__dropdown__choose__yes select-city__dropdown__choose"
                    data-entity="select-city__dropdown__choose__yes"
                    ><?= Loc::getMessage(KitRegions::moduleId . '_YES') ?>
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
<div id="regon_choose_select-city__modal" class="select-city__modal" style="display: none;">
    <div class="select-city__modal-wrap">
        <div class="select-city__close" data-entity="select-city__close"></div>
        <div class="select-city__modal__title-wrap">
            <p class="select-city__modal__title"><?= Loc::getMessage(KitRegions::moduleId . '_YOUR_CITY') ?>:
                <span data-entity="select-city__js"></span></p>
        </div>
        <div class="select-city__modal__list-wrap">
            <span class="select-city__modal__list__title"><?= Loc::getMessage(KitRegions::moduleId . '_WRONG_DETECT') ?></span>
        </div>
        <div class="select-city__modal__list" data-entity="select-city__modal__list">
            <!-- region names -->
        </div>
        <div class="select-city__modal__submit-wrap">
            <div class="select-city__modal__submit__title-wrap">
                <span class="select-city__modal__submit__title"><?= Loc::getMessage(KitRegions::moduleId . '_SELECT') ?></span>
            </div>
            <div class="select-city__modal__submit__block-wrap">
                <div class="select-city__modal__submit__block-wrap__input_wrap">
                    <div class="select-city__modal__submit__block-wrap__input_wrap_error"
                            style="display:none;"
                            data-entity="select-city__modal__submit__block-wrap__input_wrap_error"
                    ><?= Loc::getMessage(KitRegions::moduleId . '_ERROR') ?></div>
                    <input value="" type="text" class="select-city__modal__submit__input" data-entity="select-city__modal__submit__input">
                    <div class="select-city__modal__submit__vars" data-entity="select-city__modal__submit__vars" style="display: none;"></div>
                </div>
                <input type="submit" name="submit"
                        value="<?= Loc::getMessage(KitRegions::moduleId . '_SELECT_SUBMIT') ?>"
                        class="select-city__modal__submit__btn" data-entity="select-city__modal__submit__btn">
            </div>
        </div>
    </div>
</div>
<div id="regon_choose_modal__overlay" class="modal__overlay" style="display: none;"></div>
<script>
    $componentRegionsChoose = new RegionsChoose();
</script>
