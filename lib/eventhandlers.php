<?php

namespace Kit\Regions;

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Kit\Regions\Helper\Menu;

/**
 * Class EventHandlers
 *
 * @package Kit\Regions
 */
class EventHandlers
{
    public function OnBuildGlobalMenuHandler(&$arGlobalMenu, &$arModuleMenu){
        Menu::getAdminMenu($arGlobalMenu, $arModuleMenu);
    }

    /**
     * event handler of (sale,OnSaleComponentOrderProperties)
     * set location  on order page
     *
     * @param $arUserResult
     * @param $request
     * @param $arParams
     * @param $arResult
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public function OnSaleComponentOrderPropertiesHandler(
        &$arUserResult,
        $request,
        &$arParams,
        &$arResult
    ) {
        Location\EventHandlers::OnSaleComponentOrderPropertiesHandler(
            $arUserResult,
            $request,
            $arParams,
            $arResult);
    }

    /**
     * event handler of (main,OnEndBufferContent)
     * change variables and add seo tags
     *
     * @param $content
     */
    public function OnEndBufferContentHandler(&$content)
    {
        Location\EventHandlers::OnEndBufferContentHandler($content);
        Seo\AdminArea::addRegionsSeo($content);
    }

    /**
     * event handler of (main,OnUserTypeBuildList)
     * create user type property - html
     *
     * @return array
     */
    public function OnUserTypeBuildListHandlerHtml()
    {
        return UserType\Html::OnUserTypeBuildListHandler();
    }

    /**
     * event handler of (main,OnProlog)
     *
     * @throws \Bitrix\Main\SystemException
     */
    public function OnPrologHandler()
    {
        //$domain = new Location\Domain();
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $request = $context->getRequest();
        $server = $context->getServer();
        global $USER_FIELD_MANAGER;

        if ($request->getPost('MULTIPLE') && $request->getPost('USER_TYPE_ID') == 'html')
        {
            unset($_REQUEST['MULTIPLE']);
        }
        if ($server->getScriptName() == '/bitrix/admin/userfield_edit.php')
        {
            $userTypeId = $request->getQuery("USER_TYPE_ID");

            $arUserTypes = $USER_FIELD_MANAGER->GetUserType();

            if ($arUserTypes) {
                $first = reset($arUserTypes);
            }

            if (($first['USER_TYPE_ID'] == 'html' && !$userTypeId)
                || $userTypeId == 'html'
            ) {
                Asset::getInstance()->addString(
                    '<style>.adm-detail-content-table tr:nth-child(6){display:none;}</style>');
            }
        }
    }

    /**
     * event handler of (iblock,OnIBlockPropertyBuildList)
     * add type of property - regions
     *
     * @return array
     */
    public function OnIBlockPropertyBuildListHandler()
    {
        return UserType\Region::GetUserTypeDescription();
    }

    /**
     * event handler of (catalog,OnGetOptimalPrice)
     * change price before add basket
     *
     * @param        $productId
     * @param int    $qnt
     * @param array  $userGroups
     * @param string $renewal
     * @param array  $prices
     * @param bool   $siteId
     * @param bool   $coupons
     *
     * @return array|bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    public function OnGetOptimalPriceHandler(
        $productId,
        $qnt = 1,
        $userGroups = [],
        $renewal = "N",
        $prices = [],
        $siteId = false,
        $coupons = false
    ) {
        if (!Loader::includeModule('kit.regions')) {
            return true;
        }

        return Sale\EventHandlers::OnGetOptimalPriceHandler($productId, $qnt,
            $userGroups, $renewal, $prices,
            $siteId, $coupons);
    }

    /**
     * @return \Bitrix\Main\EventResult
     */
    public function onSaleDeliveryRestrictionsClassNamesBuildListHandler()
    {
        return Sale\EventHandlers::onSaleDeliveryRestrictionsClassNamesBuildListHandler();
    }

    /**
     * @return \Bitrix\Main\EventResult
     */
    public function onSalePaySystemRestrictionsClassNamesBuildListHandler()
    {
        return Sale\EventHandlers::onSalePaySystemRestrictionsClassNamesBuildListHandler();
    }

    /**
     * @param Event $event
     */
    public function OnSaleOrderBeforeSavedHandler(Event $event)
    {
        Sale\EventHandlers::OnSaleOrderBeforeSavedHandler($event);
    }

    /**
     * @param $event
     * @param $lid
     * @param $arFields
     * @param $message_id
     *
     * @throws \Bitrix\Main\SystemException
     */
    public function OnBeforeEventAddHandler(
        &$event,
        &$lid,
        &$arFields,
        &$message_id
    ) {
        Location\EventHandlers::OnBeforeEventAddHandler($event, $lid, $arFields,
            $message_id);
    }
    public function OnBeforeMailSendHandler($mailParams) {
        Location\EventHandlers::OnBeforeMailSendHandler($mailParams);
    }

    /**
     * Basket rules
     */
    public function OnCondSaleControlBuildListHandler() {
        return Sale\EventHandlers::OnCondSaleControlBuildListHandler();
    }
}