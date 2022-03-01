<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$this->createFrame()->begin("_____");
?>
<div id="regions_choose_component">
<span class="select-city__block__text-city" data-entity="open_region">
    ______
</span>
</div>
<!-- modal is yr city YES/NO popup -->
<div class="select-city__dropdown-wrap" id="regions_choose_component_dropdown"  style="display: none;">
    <div class="select-city__dropdown">
        <div class="select-city__dropdown__title-wrap">
				<span class="select-city__dropdown__title" data-entity="select-city__dropdown__title">
                    <?= Loc::getMessage(SotbitRegions::moduleId . '_YOUR_CITY') . ' ###?' ?>
				</span>
        </div>

        <div class="select-city__dropdown__choose-wrap">
				<span class="select-city__dropdown__choose__yes select-city__dropdown__choose"
                    data-entity="select-city__dropdown__choose__yes"
                >
					<?= Loc::getMessage(SotbitRegions::moduleId . '_YES') ?>
				</span>
                <span class="select-city__dropdown__choose__no select-city__dropdown__choose"
                    data-entity="select-city__dropdown__choose__no"
                >
					<?= Loc::getMessage(SotbitRegions::moduleId . '_NO') ?>
				</span>
        </div>

    </div>
</div>
<!-- modal YES/NO popup -->

<!-- REGIONS POPUP -->
<div id="regon_choose_select-city__modal" style="display: none;" class="select-city__modal">
    <div class="select-city__modal-wrap region_choose">
        <!-- POPUP TITTLE -->
        <div class="select-city__modal-title">
            <?= Loc::getMessage(SotbitRegions::moduleId . "_TITLE") ?>
            <div class="select-city__close" data-entity="select-city__close"></div>
        </div>
        <!--/ POPUP TITTLE -->
        <div class="select-city-content-wrapper">
            <div class="select-city__image">
                <img src="<?=$this->GetFolder()?>/img/choose_region.png">
            </div>
            <!-- REGION INPUT -->
            <div class="select-city__input-wrapper">
                <div class="select-city__response_wrapper">
                    <input class="select-city__input" data-entity="select-city__modal__submit__input"
                           placeholder=" <?= Loc::getMessage(SotbitRegions::moduleId . "_WRITE_SITY") ?>">
                    <div class="select-city__response" data-entity="select-city__modal__submit__vars" style="display: none;">
                    </div>
                </div>
            </div>
            <!--/ REGION INPUT -->
            <!-- CITY FOR EXAMPLE -->
            <div class="select-city__wrapper__input">
                <div class="select-city__input__comment select-city__under_input" data-entity="select-city__input__example">
                    <?=Loc::getMessage('sotbit.regions_EXAMPLE')?>
                </div>
            </div>
            <!--/ CITY FOR EXAMPLE  -->
            <!-- BUTTON -->
            <div class="select-city-button-wrapper">
                <div>
                    <button type="submit" class="btn select-city-button regions_choose"
                        data-entity="select-city__modal__submit__btn" disabled
                    >
                        <?=Loc::getMessage(SotbitRegions::moduleId . "CHOOSE_REG_BUTTON_TITTLE")?>
                    </button>
                </div>
            </div>
            <!-- / BUTTON -->
            <div class="select-city__automatic" data-entity="select-city__automatic">
                <a href="#"> <?=Loc::getMessage(SotbitRegions::moduleId . "CHOOSE_AUTOMATIC")?></a>
            </div>
        </div>
    </div>
</div>
<!--/ REGIONS POPUP -->

<div  id="regon_choose_modal__overlay" style="display: none;" class="modal__overlay"></div>

<?
?>
<script>
    $componentRegionsChoose = new RegionsChoose();
</script>
