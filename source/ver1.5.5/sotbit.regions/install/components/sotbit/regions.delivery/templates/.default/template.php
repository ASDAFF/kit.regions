<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);
Loc::loadMessages(__FILE__);
$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'regions.delivery');
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'regions.delivery');

if($arParams['AJAX'] != 'Y'){
?>
<div id="sotbit-delivery-<?=$arResult['RAND']?>" class="detailed-tabs__delivery-wrapper sotbit-delivery-root">
	<div class="detailed-tabs__delivery-detail">
	    <div class="detailed-tabs__delivery-region">
	        <p class="detailed-tabs__delivery-title">
		        <?=Loc::getMessage('sotbit.regions_DELIVERY_REGION')?>
	        </p>
	        <p class="detailed-tabs__delivery-description"><?=$arResult['USER_REGION_FULL_NAME']?></p>
	        <span class="detailed-tabs__delivery-change" data-entity="change-region">
		        <?=Loc::getMessage('sotbit.regions_DELIVERY_REGION_CHANGE')?></span>
	    </div>
		<?
		if($arResult['PAYMENT']){
			?>
			<div class="detailed-tabs__delivery-pay">
				<p class="detailed-tabs__delivery-title">
                    <?=Loc::getMessage('sotbit.regions_DELIVERY_PAYMENTS')?>
				</p>
				<div class="detailed-tabs__delivery-pay-img">
					<?
					foreach($arResult['PAYMENT'] as $payment){
						?>
						<img width="110" height="28" src="<?=$payment['LOGOTIP']['SRC']?>" alt="<?=$payment['NAME']?>">
						<?
					}
					?>
				</div>
			</div>
			<?
		}
		if($arResult['PRODUCT']['MEASURE'] || ($arResult['PRODUCT']['WIDTH'] && $arResult['PRODUCT']['HEIGHT'] && $arResult['PRODUCT']['LENGTH'])){
			?>
			<div class="detailed-tabs__delivery-box">
				<p class="detailed-tabs__delivery-title detailed-tabs__delivery-title--box">
                    <?=Loc::getMessage('sotbit.regions_DELIVERY_PRODUCT_TITLE')?>
				</p>
				<?if($arResult['PRODUCT']['MEASURE']['SYMBOL_RUS']):?>
					<p class="detailed-tabs__delivery-box-item">
                        <?=Loc::getMessage('sotbit.regions_DELIVERY_PRODUCT_MEASURE')?>: <span><?=$arResult['PRODUCT']['MEASURE']['SYMBOL_RUS']?></span>
					</p>
				<?endif;
                if($arResult['PRODUCT']['WIDTH'] && $arResult['PRODUCT']['HEIGHT'] && $arResult['PRODUCT']['LENGTH']):
				?>
					<p class="detailed-tabs__delivery-box-item"><?=Loc::getMessage('sotbit.regions_DELIVERY_PRODUCT_SIZE')?>: <span><?=$arResult['PRODUCT']['WIDTH']?><?=Loc::getMessage('sotbit.regions_DELIVERY_PRODUCT_SM')?> õ <?=$arResult['PRODUCT']['HEIGHT']?><?=Loc::getMessage('sotbit.regions_DELIVERY_PRODUCT_SM')?> õ <?=$arResult['PRODUCT']['LENGTH']?><?=Loc::getMessage('sotbit.regions_DELIVERY_PRODUCT_SM')?></span></p>
                <?endif;
                if($arResult['PRODUCT']['WEIGHT']):
                ?>
					<p class="detailed-tabs__delivery-box-item"><?=Loc::getMessage('sotbit.regions_DELIVERY_PRODUCT_WEIGHT')?>: <span><?=$arResult['PRODUCT']['WEIGHT']?> <?=Loc::getMessage('sotbit.regions_DELIVERY_PRODUCT_KG')?></span></p>
                <?endif;?>
			</div>
			<?
		}
		?>
	</div>
	<?
    if($arResult['DELIVERY']){
	?>
	<div class="detailed-tabs__delivery-way">
		<?
        foreach($arResult['DELIVERY'] as $delivery){
            ?>
			<div class="detailed-tabs__delivery-way-item delivery-way-item">
				<div class="delivery-way-item__img">
                    <?
                    if($delivery['LOGOTIP']['SRC']){
                        ?>
						<img src="<?=$delivery['LOGOTIP']['SRC']?>" alt="<?=$delivery['NAME']?>" title="<?=$delivery['NAME']?>">
                        <?
                    }
                    else{
                        ?>
						<img src="<?=$this->__component->__template->__folder?>/img/box-default.png" alt="<?=$delivery['NAME']?>" title="<?=$delivery['NAME']?>">
                        <?
                    }
                    ?>

				</div>
				<div class="delivery-way-item__description">
                    <?if($delivery['NAME']):?>
						<p class="delivery-way-item__title"><?=$delivery['NAME']?></p>
                    <?endif;?>
                    <?if($delivery['TIME']):?>
						<p class="delivery-way-item__time"><?=Loc::getMessage('sotbit.regions_DELIVERY_TIME')?>: <span><?=$delivery['TIME']?></span></p>
                    <?endif;?>
                    <?if($delivery['DESCRIPTION']):?>
						<p class="delivery-way-item__message"><?=$delivery['DESCRIPTION']?></p>
                    <?endif;?>
				</div>
				<?
				if($delivery['PRINT_PRICE']):
				?>
				<div class="delivery-way-item__price">
					<p class="delivery-way-item__price-summ"><?=$delivery['PRINT_PRICE']?></p>
				</div>
				<?endif;?>
			</div>
            <?
        }
		?>
	</div>
	<?}?>
	<div class="select-city__modal" data-entity="modal">
		<div class="select-city__modal-wrap">
			<div class="select-city__close" data-entity="close"></div>
	        <?
	        if($arResult['REGION_LIST_COUNTRIES']){
	            ?>
				<div class="select-city__tabs_wrapper">
					<ul class="select-city__tabs" id="sotbit-regions-tabs">
	                    <?
	                    foreach ($arResult['REGION_LIST_COUNTRIES'] as $idCountry => $region){
	                        if($region['SALE_LOCATION_LOCATION_NAME_NAME']) {
	                            ?>
								<li class="select-city__tab <?= ($idCountry
	                                == key($arResult['REGION_LIST_COUNTRIES'])) ? 'active'
	                                : '' ?>" data-country-id="<?= $idCountry ?>" data-entity="country">
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
	                <?=$arResult['USER_REGION_FULL_NAME']?>
				</p>
			</div>

			<input class="select-city__input" type="region-input" name="" id="region-input" data-entity="filter-regions">

	        <?
	        if($arResult['TITLE_CITIES']) {
	            ?>
				<div class="select-city__wrapper__input">
					<div class="select-city__input__comment select-city__under_input">
	                    <?= Loc::getMessage(
	                        'sotbit.regions_DELIVERY_EXAMPLE',
	                        [
	                            '#ID0#'   => $arResult['TITLE_CITIES'][0]['ID'],
	                            '#ID1#'   => $arResult['TITLE_CITIES'][1]['ID'],
	                            '#NAME0#' => $arResult['TITLE_CITIES'][0]['SALE_LOCATION_LOCATION_NAME_NAME'],
	                            '#NAME1#' => $arResult['TITLE_CITIES'][1]['SALE_LOCATION_LOCATION_NAME_NAME'],
	                        ]
	                    ) ?>
					</div>
				</div>
	            <?
	        }
	        if($arResult['REGION_LIST_COUNTRIES']){
	            foreach ($arResult['REGION_LIST_COUNTRIES'] as $id => $region){
	                ?>
					<div class="select-city__tab_content <?=($id == key($arResult['REGION_LIST_COUNTRIES']))?'active':''?>" data-country-id="<?=$id?>" data-entity="tab-content">
						<div class="select-city__list_wrapper">
	                        <?if($arResult['FAVORITES'][$id]){
	                            ?>
								<div class="select-city__list_wrapper select-city__list_wrapper_favorites">
									<div class="select-city__list">
	                                    <?
	                                    foreach($arResult['FAVORITES'][$id] as $city){
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
								<div class="select-city__list_wrapper select-city__list_wrapper_cities">
	                                <?
	                                foreach ($region['CITY'] as $letter => $cities){
	                                    ?>
										<div class="select-city__list_letter_wrapper" data-entity="letter">
											<div class="select-city__list_letter">
	                                            <?=$letter?>
											</div>
											<div class="select-city__list" data-entity="item-list">
	                                            <?
	                                            foreach ($cities as $city){
	                                                ?>
													<p class="select-city__list_item" data-entity="item" data-index="<?= $city['CODE'] ?>">
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
	</div>
	<div class="modal__overlay" data-entity="overlay"></div>
</div>
<script>
	var RegionsDelivery = new SotbitRegionsDelivery({
		'root':'sotbit-delivery-<?=$arResult['RAND']?>',
		'componentPath': '<?=CUtil::JSEscape($componentPath)?>',
		'templatePath': '<?=CUtil::JSEscape($templateFolder)?>',
		'parameters': '<?=CUtil::JSEscape($signedParams)?>',
		'siteId': '<?=CUtil::JSEscape($component->getSiteId())?>',
		'template': '<?=CUtil::JSEscape($signedTemplate)?>',
	});
</script>
<?}?>
