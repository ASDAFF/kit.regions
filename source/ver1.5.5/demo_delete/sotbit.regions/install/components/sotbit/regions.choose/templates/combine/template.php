<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Config\Option;

Loc::loadMessages(__FILE__);

?>
<svg width="18" height="18">
    <use xlink:href="/local/templates/sotbit_origami/assets/img/sprite.svg#icon_location"></use>
</svg>
<p><?= Loc::getMessage(SotbitRegions::moduleId . '_YOUR_CITY') ?></p>
<a href="#" class="select-city__block__text-city__js" onclick="function f(e) {	  e.preventDefault();	}">
    <?=$arResult['USER_REGION_NAME']?>
</a>

<!-- modal is yr city YES/NO popup -->
<div class="select-city__dropdown-wrap"  style="<?=($arResult['SHOW_POPUP'] == 'Y')?'display:block;':'display:none;'?>">
    <div class="select-city__dropdown">
        <div class="select-city__dropdown__title-wrap">
				<span class="select-city__dropdown__title"><?= Loc::getMessage(SotbitRegions::moduleId . '_YOUR_CITY') ?>
                    <?= $arResult['USER_REGION_NAME_LOCATION'] ?>?
				</span>
        </div>

        <div class="select-city__dropdown__choose-wrap">
				<span class="select-city__dropdown__choose__yes select-city__dropdown__choose" data-id="<?=$arResult['USER_REGION_ID']?>" data-region-id="<?=$arResult['USER_MULTI_REGION_ID']?>" data-code="<?= $arResult['USER_REGION_CODE']?>">
					<?= Loc::getMessage(SotbitRegions::moduleId . '_YES') ?>
				</span>
                <span class="select-city__dropdown__choose__no select-city__dropdown__choose">
					<?= Loc::getMessage(SotbitRegions::moduleId . '_NO') ?>
				</span>
        </div>

    </div>
</div>
<!-- modal YES/NO popup -->

<!-- REGIONS POPUP -->
<div class="select-city__modal">

</div>
<!--/ REGIONS POPUP -->

<div class="modal__overlay"></div>

<?
?>
<script>
    var SotbitRegion = new SotbitRegions({
        'list':<?=CUtil::PhpToJSObject($arResult['REGION_LIST'], false, true); ?>,
        'rootDomain':'<?=$arResult['ROOT_DOMAIN'][0]?>',
        //'rootDomain': 'sotbit.com',
        'templateFolder':'<?=$templateFolder?>',
        'componentFolder':'<?=$componentPath?>',
        'singleDomain':'<?=Option::get('SINGLE_DOMAIN',SITE_ID)?>',
        'arParams':'<?=json_encode($arParams)?>',
        'requestFolder': '/local/templates/.default/components/sotbit/regions.choose/origami_header_2',
        'locationType': <?if($arParams["FROM_LOCATION"] == "Y"):?>'location'<?else:?>'regions'<?endif;?>,
    });
</script>
