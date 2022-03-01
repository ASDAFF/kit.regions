<?
use Bitrix\Main\Localization\Loc;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!\Bitrix\Main\Loader::includeModule('sotbit.regions')){
    return false;
}

Loc::loadMessages(__FILE__);
$regions = \Sotbit\Regions\System\Location::getLocations();
?>
<div class="select-city__modal-wrap">
    <div class="select-city__close"></div>
    <?
    if($regions['REGION_LIST_COUNTRIES']){
        ?>
        <div class="select-city__tabs_wrapper">
            <ul class="select-city__tabs" id="sotbit-regions-tabs">
                <?
                foreach ($regions['REGION_LIST_COUNTRIES'] as $idCountry => $region){
                    if($region['SALE_LOCATION_LOCATION_NAME_NAME']) {
                        ?>
                        <li class="select-city__tab <?= ($idCountry
                            == key($regions['REGION_LIST_COUNTRIES'])) ? 'active'
                            : '' ?>" data-country-id="<?= $idCountry ?>">
                            <?= $region['SALE_LOCATION_LOCATION_NAME_NAME'] ?>
                        </li>
                        <?
                    }
                }
                ?>
            </ul>
        </div>
        <?
    }

     ?>
    <div class="select-city__modal__title-wrap">
        <p class="select-city__modal__title">
            <?=$regions['USER_REGION_FULL_NAME']?>
        </p>
    </div>

    <input class="select-city__input" type="region-input" name="" id="region-input" placeholder="<?=Loc::getMessage(SotbitRegions::moduleId.'_WRITE_SITY')?>">

    <?
    if($regions['TITLE_CITIES']) {
        ?>
        <div class="select-city__wrapper__input">
            <div class="select-city__input__comment select-city__under_input">
                <?= Loc::getMessage(
                    'sotbit.regions_EXAMPLE',
                    [
                        '#ID0#'   => $regions['TITLE_CITIES'][0]['ID'],
                        '#ID1#'   => $regions['TITLE_CITIES'][1]['ID'],
                        '#NAME0#' => $regions['TITLE_CITIES'][0]['SALE_LOCATION_LOCATION_NAME_NAME'],
                        '#NAME1#' => $regions['TITLE_CITIES'][1]['SALE_LOCATION_LOCATION_NAME_NAME'],
                    ]
                ) ?>
            </div>
        </div>
        <?
    }
    if($regions['REGION_LIST_COUNTRIES']){
    	?>
	    <div class="select-city__tab_name_content">
            <?php
            if($regions['FAVORITES']){
                ?>
			    <div class="select-city__tab_name_content__big_city"><?=Loc::getMessage(SotbitRegions::moduleId.'_BIG_CITIES')?></div>
                <?php
            }
            ?>
		    <div class="select-city__tab_name_content__village"><?=Loc::getMessage(SotbitRegions::moduleId.'_CITIES')?></div>
	    </div>
	    <?
        foreach ($regions['REGION_LIST_COUNTRIES'] as $id => $region){
            ?>
            <div class="select-city__tab_content <?=($id == key($regions['REGION_LIST_COUNTRIES']))?'active':''?>" data-country-id="<?=$id?>">
                <div class="select-city__list_wrapper">
                    <?if($regions['FAVORITES'][$id]){
                        ?>
                        <div class="select-city__list_wrapper_favorites">
                            <div class="select-city__list">
                                <?
                                foreach($regions['FAVORITES'][$id] as $city){
                                    ?>
                                    <p class="select-city__list_item" data-index="<?= $city['ID'] ?>">
                                        <?= $city['SALE_LOCATION_LOCATION_NAME_NAME'] ?>
                                    </p>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                        <?
                    }
                    if($region['CITY']){
                        ?>
                        <div class="select-city__list_wrapper_cities">
                            <?
                            foreach ($region['CITY'] as $letter => $cities){
                                ?>
                                <div class="select-city__list_letter_wrapper">
                                    <div class="select-city__list_letter">
                                        <?=$letter?>
                                    </div>
                                    <div class="select-city__list">
                                        <?
                                        foreach ($cities as $city){
                                            ?>
                                            <p class="select-city__list_item" data-index="<?= $city['ID'] ?>">
                                                <?= $city['SALE_LOCATION_LOCATION_NAME_NAME'] ?>
                                            </p>
                                            <?
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>