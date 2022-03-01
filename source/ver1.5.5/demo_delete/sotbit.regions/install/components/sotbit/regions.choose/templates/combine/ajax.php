<?php
define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Sale\Location;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!\Bitrix\Main\Loader::includeModule('sotbit.regions')) {
    return false;
}

Loader::includeModule('sale');


global $APPLICATION;
$APPLICATION->RestartBuffer();

Loc::loadMessages(__FILE__);
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if($request->get('action') == 'getAutoRegion')
{
    \Sotbit\Regions\Location\Domain::$autoDef = true;
    $arRegion = \Sotbit\Regions\Location\Domain::getAutoRegion(true);
    exit(\Bitrix\Main\Web\Json::encode($arRegion));
}

if ($request->get('type') == 'regions')
    $randRegions = \Sotbit\Regions\Region::getRandRegions(2);

if ($request->get('type') == 'location')
    $randRegions = \Sotbit\Regions\System\Location::getRandLocations(2);

if($randRegions)
    $randRegions = array_values($randRegions);
//if($request->isAjaxRequest())
$path = $APPLICATION->GetCurDir();
{
    $template = '
    <div class="select-city__modal-wrap region_choose">
        <!-- POPUP TITTLE -->
        <div class="select-city__modal-title">' . Loc::getMessage(SotbitRegions::moduleId . "_TITLE") . '<div class="select-city__close"></div>
        </div>
        <!--/ POPUP TITTLE -->
        <div class="select-city-content-wrapper">
            <div class="select-city__image">
                <img src="'. $path .'img/choose_region.png">
            </div>
            <!-- REGION INPUT -->
            <div class="select-city__input-wrapper">
                <div class="select-city__response_wrapper">
                    <input class="select-city__input" type="region-input" name="" id="region-input"
                           placeholder="' . Loc::getMessage(SotbitRegions::moduleId . "_WRITE_SITY") . '">
                    <div class="select-city__response">
                    </div>
                </div>
            </div>
            <!--/ REGION INPUT -->
            <!-- CITY FOR EXAMPLE -->
            <div class="select-city__wrapper__input">
                <div class="select-city__input__comment select-city__under_input">' . Loc::getMessage(
                    'sotbit.regions_EXAMPLE',
                    [
                        '#ID0#'   => $randRegions[0]['ID'],
                        '#ID1#'   => $randRegions[1]['ID'],
                        '#LOC_ID0#'   => $randRegions[0]['LOC_ID'],
                        '#LOC_ID1#'   => $randRegions[1]['LOC_ID'],
                        '#CODE0#'   => $randRegions[0]['CODE'],
                        '#CODE1#'   => $randRegions[1]['CODE'],
                        '#NAME0#' => $randRegions[0]['NAME'],
                        '#NAME1#' => $randRegions[1]['NAME'],
                    ]
                ) . '</div>
            </div>
            <!--/ CITY FOR EXAMPLE  -->
            <!-- BUTTON -->
            <div class="select-city-button-wrapper">
                <div>
                    <button type="submit" class="btn select-city-button regions_choose"
                          disabled>' . Loc::getMessage(SotbitRegions::moduleId . "CHOOSE_REG_BUTTON_TITTLE") . '</button>
                </div>
            </div>
            <!-- / BUTTON -->
            <div class="select-city__automatic">
                <a href="#">' . Loc::getMessage(SotbitRegions::moduleId . "CHOOSE_AUTOMATIC") . '</a>
            </div>
        </div>
    </div>
';

    if ($request->get('type') == 'regions')
    {
        if($request->get('getBase') == 'Y')
            $arRegions = \Sotbit\Regions\Region::getAllRegions();

        exit(\Bitrix\Main\Web\Json::encode(array(
            'TEMPLATE' => $template,
            'LOCATION' => $arRegions
        )));

    } elseif ($request->get('type') == 'location')
    {
        $arLocations = [];
        if($request->get('getBase') == 'Y')
            $arLocations = \Sotbit\Regions\System\Location::getAllLocations();
        exit(\Bitrix\Main\Web\Json::encode(array(
            'TEMPLATE' => $template,
            'LOCATION' => $arLocations
        )));
    }
}
exit(json_encode(array()));

