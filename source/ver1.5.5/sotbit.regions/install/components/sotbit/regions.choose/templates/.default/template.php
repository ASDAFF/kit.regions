<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Config\Option;
use Sotbit\Regions\Internals\RegionsTable;

Loc::loadMessages(__FILE__);
$allRegions = RegionsTable::GetList(array(
    'order' => array("ID" => "asc"),
))->fetchAll();

if($allRegions === false && $USER->isAdmin()){
    echo Loc::getMessage(SotbitRegions::moduleId . '_REGION_TABLE_IS_EMPTY');
} else if($allRegions !== false) {
    $this->setFrameMode(true);
    $frame = $this->createFrame()->begin("");
    ?>
    <div class="select-city-wrap">
        <div class="select-city__block">
            <span class="select-city__block__text"><?= Loc::getMessage(SotbitRegions::moduleId . '_YOUR_CITY') ?>: </span>
            <span class="select-city__block__text-city"><?= $arResult['USER_REGION_NAME'] ?></span>
        </div>
        <div class="select-city__dropdown-wrap"
             style="<?= ($arResult['SHOW_POPUP'] == 'Y') ? 'display:block;' : 'display:none;' ?>">
            <div class="select-city__dropdown">
                <div class="select-city__dropdown__title-wrap">
				<span class="select-city__dropdown__title"><?= Loc::getMessage(SotbitRegions::moduleId . '_YOUR_CITY') ?>
                    <?= $arResult['USER_REGION_NAME_LOCATION'] ?>?</span>
                </div>
                <div class="select-city__dropdown__choose-wrap">
                    <span class="select-city__dropdown__choose__yes select-city__dropdown__choose"
                          data-id="<?= $arResult['USER_REGION_ID'] ?>"><?= Loc::getMessage(SotbitRegions::moduleId . '_YES') ?></span>
                    <span class="select-city__dropdown__choose__no select-city__dropdown__choose"><?= Loc::getMessage(SotbitRegions::moduleId . '_NO') ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="select-city__modal">
        <div class="select-city__modal-wrap">
            <div class="select-city__close"></div>
            <div class="select-city__modal__title-wrap">
                <p class="select-city__modal__title"><?= Loc::getMessage(SotbitRegions::moduleId . '_YOUR_CITY') ?>:
                    <span id="select-city__js"><?= $arResult['USER_REGION_NAME'] ?></span></p>
            </div>
            <div class="select-city__modal__list-wrap">
                <span class="select-city__modal__list__title"><?= Loc::getMessage(SotbitRegions::moduleId . '_WRONG_DETECT') ?></span>
            </div>
            <div class="select-city__modal__list">
                <?php
                foreach ($arResult['REGION_LIST'] as $i => $region) {
                    if ($i > 14) {
                        break;
                    }
                    ?>
                    <p class="select-city__modal__list__item" data-index="<?= $i ?>"><?= $region['NAME'] ?></p>
                    <?php
                }
                ?>
            </div>
            <div class="select-city__modal__submit-wrap">
                <div class="select-city__modal__submit__title-wrap">
                    <span class="select-city__modal__submit__title"><?= Loc::getMessage(SotbitRegions::moduleId . '_SELECT') ?></span>
                </div>
                <div class="select-city__modal__submit__block-wrap">
                    <div class="select-city__modal__submit__block-wrap__input_wrap">
                        <div class="select-city__modal__submit__block-wrap__input_wrap_error"
                             style="display:none;"><?= Loc::getMessage(SotbitRegions::moduleId . '_ERROR') ?></div>
                        <input value="" type="text" class="select-city__modal__submit__input">
                    </div>
                    <input type="submit" name="submit"
                           value="<?= Loc::getMessage(SotbitRegions::moduleId . '_SELECT_SUBMIT') ?>"
                           class="select-city__modal__submit__btn">
                </div>
            </div>
        </div>
    </div>
    <div class="modal__overlay"></div>
    <script>
        var SotbitRegion = new SotbitRegions({
            'list':<?=CUtil::PhpToJSObject($arResult['REGION_LIST'], false, true); ?>,
            'rootDomain': '<?=$arResult['ROOT_DOMAIN'][0]?>',
            'templateFolder': '<?=$templateFolder?>',
            'componentFolder': '<?=$componentPath?>',
            'singleDomain': '<?=Option::get('SINGLE_DOMAIN', SITE_ID)?>',
            'arParams': '<?=json_encode($arParams)?>'
        });
    </script>
    <? $frame->end();
}?>