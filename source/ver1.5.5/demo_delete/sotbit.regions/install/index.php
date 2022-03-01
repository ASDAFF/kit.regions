<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SiteTable;
use Bitrix\Sale\Location\TypeTable;

Loc::loadMessages(__FILE__);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client_partner.php');

class sotbit_regions extends CModule
{
    const MODULE_ID = 'sotbit.regions';
    var $MODULE_ID = 'sotbit.regions';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $_2092170183 = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage(self::MODULE_ID . '_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage(self::MODULE_ID . '_MODULE_DESC');
        $this->PARTNER_NAME = GetMessage('sotbit.regions_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('sotbit.regions_PARTNER_URI');
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        if ($_REQUEST['step'] == 1) {
            ModuleManager::registerModule(self::MODULE_ID);
            $this->InstallEvents();
        } else {
            $this->InstallFiles();
            $this->InstallDB();
            $APPLICATION->IncludeAdminFile(GetMessage('INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sotbit.regions/install/step.php');
        }
    }

    function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler('sale', 'OnSaleComponentOrderProperties', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnSaleComponentOrderPropertiesHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnEndBufferContent', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnEndBufferContentHandler', 999);
        EventManager::getInstance()->registerEventHandler('main', 'OnUserTypeBuildList', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnUserTypeBuildListHandlerHtml');
        EventManager::getInstance()->registerEventHandler('main', 'OnProlog', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnPrologHandler');
        EventManager::getInstance()->registerEventHandler('iblock', 'OnIBlockPropertyBuildList', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnIBlockPropertyBuildListHandler');
        EventManager::getInstance()->registerEventHandler('catalog', 'OnGetOptimalPrice', 'main', '\Sotbit\Regions\EventHandlers', 'OnGetOptimalPriceHandler', 100, '/modules/sotbit.regions/lib/eventhandlers.php');
        EventManager::getInstance()->registerEventHandler('sale', 'onSaleDeliveryRestrictionsClassNamesBuildList', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'onSaleDeliveryRestrictionsClassNamesBuildListHandler');
        EventManager::getInstance()->registerEventHandler('sale', 'onSalePaySystemRestrictionsClassNamesBuildList', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'onSalePaySystemRestrictionsClassNamesBuildListHandler');
        EventManager::getInstance()->registerEventHandler('sale', 'OnSaleOrderBeforeSaved', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnSaleOrderBeforeSavedHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeEventAdd', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnBeforeEventAddHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeMailSend', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnBeforeMailSendHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBuildGlobalMenu', self::MODULE_ID, '\Sotbit\Regions\EventHandlers', 'OnBuildGlobalMenuHandler');
        EventManager::getInstance()->registerEventHandler('sale', 'OnCondSaleControlBuildList', self::MODULE_ID, '\Sotbit\Regions\EventHandlers', 'OnCondSaleControlBuildListHandler');
        $_966293903 = SiteTable::getList(array('filter' => array('ACTIVE' => 'Y')));
        while ($_903582902 = $_966293903->fetch()) {
            $this->SotbitRegionsInstallData($_903582902['LID']);
            $this->SotbitRegionsSetSettings($_903582902['LID']);
        }
        return true;
    }

    function SotbitRegionsInstallData($_824762740 = '')
    {
        $this->SotbitRegionsInstallProperties($_824762740);
        $this->SotbitRegionsInstallDomains($_824762740);
        $this->SotbitRegionsInstallFavorites($_824762740);
        return true;
    }

    function SotbitRegionsInstallProperties($_824762740)
    {
        $_1501769227 = array();
        $_124686194 = CLanguage::GetList($_436732177, $_658653442, array());
        while ($_272330083 = $_966293903 = $_124686194->Fetch()) {
            $_1501769227[] = htmlspecialcharsbx($_272330083['LID']);
        }
        $_1622258214 = new \CUserTypeEntity;
        $_1936986892 = array('ENTITY_ID' => 'SOTBIT_REGIONS', 'SORT' => 100, 'MULTIPLE' => 'N', 'MANDATORY' => 'N', 'SHOW_FILTER' => 'N', 'SHOW_IN_LIST' => 'Y', 'EDIT_IN_LIST' => 'Y', 'IS_SEARCHABLE' => 'N', 'SETTINGS' => array(), 'EDIT_FORM_LABEL' => array(), 'LIST_COLUMN_LABEL' => array(), 'LIST_FILTER_LABEL' => array(), 'ERROR_MESSAGE' => array(), 'HELP_MESSAGE' => array(),);
        $_381262681 = array('PHONE' => array('FIELD_NAME' => 'UF_PHONE', 'USER_TYPE_ID' => 'string', 'MULTIPLE' => 'Y'), 'ADDRESS' => array('FIELD_NAME' => 'UF_ADDRESS', 'USER_TYPE_ID' => 'html',), 'EMAIL' => array('FIELD_NAME' => 'UF_EMAIL', 'USER_TYPE_ID' => 'string', 'MULTIPLE' => 'Y'), 'ROBOTS' => array('FIELD_NAME' => 'UF_ROBOTS', 'USER_TYPE_ID' => 'html',),);
        foreach ($_381262681 as $_1788443699 => $_1438710370) {
            $_1135003077 = array_merge($_1936986892, $_1438710370);
            foreach ($_1501769227 as $_635616548) {
                $_1135003077['EDIT_FORM_LABEL'][$_635616548] = Loc::getMessage('sotbit.regions_PROP_' . $_1788443699);
                $_1135003077['LIST_COLUMN_LABEL'][$_635616548] = Loc::getMessage('sotbit.regions_PROP_' . $_1788443699);
                $_1135003077['LIST_FILTER_LABEL'][$_635616548] = Loc::getMessage('sotbit.regions_PROP_' . $_1788443699);
                $_1135003077['ERROR_MESSAGE'][$_635616548] = Loc::getMessage('sotbit.regions_PROP_' . $_1788443699);
                $_1135003077['HELP_MESSAGE'][$_635616548] = Loc::getMessage('sotbit.regions_PROP_' . $_1788443699);
            }
            $_188454216 = $_1622258214->Add($_1135003077);
        }
    }

    function SotbitRegionsInstallDomains($_824762740)
    {
        $_538562351 = array();
        $_2047988612 = array();
        if (Loader::includeModule('catalog')) {
            $_966293903 = \CCatalogGroup::GetList(array(), array('ACTIVE' => 'Y'));
            while ($_525398833 = $_966293903->Fetch()) {
                $_538562351[] = $_525398833['NAME'];
            }
            $_966293903 = \CCatalogStore::GetList(array(), array('ISSUING_CENTER' => 'Y', 'ACTIVE' => 'Y'), false, false, array('ID'));
            while ($_1365007376 = $_966293903->Fetch()) {
                $_2047988612[] = $_1365007376['ID'];
            }
        }
        $_653977542 = array('', 'spb', 'sochi', 'pyatigorsk', 'voronezh', 'krasnodar', 'samara', 'rostov', 'ufa', 'kaluga', 'kazan', 'stavropol', 'nn');
        $_1079604733 = Bitrix\Main\Application::getInstance()->getContext();
        $_513368146 = $_1079604733->getServer();
        $_628438553 = $_513368146->getServerName();
        $_1863160785 = (!empty($_SERVER['HTTPS']) && 'off' !== mb_strtolower($_SERVER['HTTPS'])) ? 'https://' : 'http://';
        global $DB;
        foreach ($_653977542 as $_769059950 => $_1144031327) {
            if (!empty($_1144031327)) {
                $_531056557 = $_1144031327 . '.';
            } else {
                $_531056557 = $_1144031327;
            }
            $_1006114811 = $_1863160785 . $_531056557 . $_628438553;
            $_334465713 = array('CODE' => $_1006114811, 'NAME' => Loc::getMessage('sotbit.regions_DOMEN_' . $_1144031327), 'SORT' => 100, 'PRICE_CODE' => $_538562351, 'STORE' => $_2047988612, 'SITE_ID' => [$_824762740]);
            if ($_769059950 == 0) $_1172023494 = '\'Y\''; else $_1172023494 = 'NULL';
            $DB->Query("INSERT INTO `sotbit_regions` VALUES (NULL,'" . $_334465713["CODE"] . "', '" . $_334465713["NAME"] . "', 100, '" . serialize($_334465713["SITE_ID"]) . "', '" . serialize($_334465713["PRICE_CODE"]) . "', '" . serialize($_334465713["STORE"]) . "', NULL, NULL, NULL, NULL, NULL, NULL, " . $_1172023494 . ");");
            $_1275601142 = intval($DB->LastID());
            if ($_1275601142 > 0) {
                $_1329195353 = '+7495';
                switch ($_1144031327) {
                    case 'spb':
                        $_1329195353 = '+7 812 ';
                        break;
                    case 'sochi':
                        $_1329195353 = '+7 8622 ';
                        break;
                    case 'pyatigorsk':
                        $_1329195353 = '+7 8793 ';
                        break;
                    case 'voronezh':
                        $_1329195353 = '+7 4732 ';
                        break;
                    case 'krasnodar':
                        $_1329195353 = '+7 861 ';
                        break;
                    case 'samara':
                        $_1329195353 = '+7 846 ';
                        break;
                    case 'rostov':
                        $_1329195353 = '+7 863 ';
                        break;
                    case 'ufa':
                        $_1329195353 = '+7 347 ';
                        break;
                    case 'kaluga':
                        $_1329195353 = '+7 4842 ';
                        break;
                    case 'kazan':
                        $_1329195353 = '+7 843 ';
                        break;
                    case 'stavropol':
                        $_1329195353 = '+7 8652 ';
                        break;
                    case 'nn':
                        $_1329195353 = '+7 831 ';
                        break;
                }
                if (mb_strlen($_1329195353) == 9) {
                    $_485137891 = array($_1329195353 . '111-11-11', $_1329195353 . '222-22-22');
                } else {
                    $_485137891 = array($_1329195353 . '11-11-11', $_1329195353 . '22-22-22');
                }
                $DB->Query("INSERT INTO `sotbit_regions_fields` VALUES (NULL," . $_1275601142 . ", 'UF_PHONE', '" . serialize($_485137891) . "');");

                $DB->Query("INSERT INTO `sotbit_regions_fields` VALUES (NULL," . $_1275601142 . ", 'UF_ADDRESS', '" . Loc::getMessage("sotbit.regions_ADDRESS", array("#HOME#" => rand(1, 50))) . "');");
                $DB->Query("INSERT INTO `sotbit_regions_fields` VALUES (NULL," . $_1275601142 . ", 'UF_EMAIL', '" . serialize(["sales@" . $_531056557 . $_628438553]) . "');");
                $DB->Query("INSERT INTO `sotbit_regions_fields` VALUES (NULL," . $_1275601142 . ", 'UF_ROBOTS', '');");
                if (Loader::includeModule('sale')) {
                    $_111507430 = \Bitrix\Sale\Location\LocationTable::getList(['filter' => ['=NAME.NAME' => Loc::getMessage('sotbit.regions_LOCATION_' . $_1144031327), '=NAME.LANGUAGE_ID' => LANGUAGE_ID,], 'select' => ['*', 'NAME.*',], 'cache' => ['ttl' => 36000000,],])->fetch();
                    if ($_111507430['ID'] > 0) {
                        $DB->Query("INSERT INTO `sotbit_regions_locations` VALUES (NULL," . $_1275601142 . "," . $_111507430["ID"] . ");");
                    }
                    if ($_1144031327 == '') {
                        $_111507430 = \Bitrix\Sale\Location\LocationTable::getList(['filter' => ['=NAME.NAME' => Loc::getMessage('sotbit.regions_LOCATION_MOSCOW'), '=NAME.LANGUAGE_ID' => LANGUAGE_ID,], 'select' => ['*', 'NAME.*',], 'cache' => ['ttl' => 36000000,],])->fetch();
                    }
                    if ($_111507430['ID'] > 0) {
                        $DB->Query("INSERT INTO `sotbit_regions_locations` VALUES (NULL," . $_1275601142 . "," . $_111507430["ID"] . ");");
                    }
                }
            }
        }
    }

    function SotbitRegionsInstallFavorites($_824762740)
    {
        if (Loader::includeModule('sale')) {
            $_1739999981 = ['MOSCOW', 'KALUGA', 'KAZAN', 'KRASNODAR', 'NN', 'PYATIGORSK', 'ROSTOV_NA_DONY', 'SAMARA', 'SOCHI', 'SP', 'STAVROPOL', 'UFA', 'VORONEG',];
            foreach ($_1739999981 as $_674772327) {
                $_111507430 = \Bitrix\Sale\Location\LocationTable::getList(['filter' => ['=NAME.NAME' => Loc::getMessage('sotbit.regions_FAVORITE_' . $_674772327), '=NAME.LANGUAGE_ID' => LANGUAGE_ID,], 'select' => ['*', 'NAME.*',], 'cache' => ['ttl' => 36000000,],])->fetch();
                if ($_111507430['CODE']) {
                    $_1944727793 = \Bitrix\Sale\Location\DefaultSiteTable::getList(['filter' => ['LOCATION_CODE' => $_111507430['CODE'], 'SITE_ID' => $_824762740]])->getSelectedRowsCount();
                    if ($_1944727793 == 0) \Bitrix\Sale\Location\DefaultSiteTable::add(['SORT' => 100, 'LOCATION_CODE' => $_111507430['CODE'], 'SITE_ID' => $_824762740]);
                }
            }
        }
    }

    function SotbitRegionsSetSettings($_824762740)
    {
        global $DB;
        $DB->Query("INSERT INTO `sotbit_regions_options` VALUES ('SINGLE_DOMAIN','Y', '" . $_824762740 . "');");
        if (Loader::includeModule('statistic')) {
            $DB->Query("INSERT INTO `sotbit_regions_options` VALUES ('FIND_USER_METHOD','statistic', '" . $_824762740 . "');");
        } elseif (function_exists('curl_version')) {
            $DB->Query("INSERT INTO `sotbit_regions_options` VALUES ('FIND_USER_METHOD','ipgeobase', '" . $_824762740 . "');");
        } else {
            $DB->Query("INSERT INTO `sotbit_regions_options` VALUES ('FIND_USER_METHOD','geoip','" . $_824762740 . "');");
        }
        $DB->Query("INSERT INTO `sotbit_regions_options` VALUES ('INSERT_SALE_LOCATION','N', '" . $_824762740 . "');");
        $DB->Query("INSERT INTO `sotbit_regions_options` VALUES ('MULTIPLE_DELIMITER',', ', '" . $_824762740 . "');");
        $_1618154071 = 5;
        if (Loader::includeModule('sale')) {
            $_1346233108 = TypeTable::getList(['select' => ['ID'], 'filter' => ['CODE' => 'CITY']])->fetch();
            if (!empty($_1346233108['ID'])) $_1618154071 = $_1346233108['ID'];
        }
        $DB->Query("INSERT INTO `sotbit_regions_options` VALUES ('LOCATION_TYPE', '" . $_1618154071 . "', '" . $_824762740 . "');");
    }

    function InstallFiles($_196321995 = array())
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true);
        if (is_dir($_1543859195 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default')) {
            if ($_1852328860 = opendir($_1543859195)) {
                while (false !== $_116833994 = readdir($_1852328860)) {
                    if ($_116833994 == '..' || $_116833994 == '.') continue;
                    CopyDirFiles($_1543859195 . '/' . $_116833994, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/' . $_116833994, $_1278828162 = True, $_1847141940 = True);
                }
                closedir($_1852328860);
            }
        }
        if (is_dir($_1543859195 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($_1852328860 = opendir($_1543859195)) {
                while (false !== $_116833994 = readdir($_1852328860)) {
                    if ($_116833994 == '..' || $_116833994 == '.') continue;
                    CopyDirFiles($_1543859195 . '/' . $_116833994, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $_116833994, $_1278828162 = True, $_1847141940 = True);
                }
                closedir($_1852328860);
            }
        }
        if (is_dir($_1543859195 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/local')) {
            if ($_1852328860 = opendir($_1543859195)) {
                while (false !== $_116833994 = readdir($_1852328860)) {
                    if ($_116833994 == '..' || $_116833994 == '.') continue;
                    CopyDirFiles($_1543859195 . '/' . $_116833994, $_SERVER['DOCUMENT_ROOT'] . '/local/' . $_116833994, $_1278828162 = True, $_1847141940 = True);
                }
                closedir($_1852328860);
            }
        }
        return true;
    }

    function installDB()
    {
        global $DB;
        $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/db/' . mb_strtolower($DB->type) . '/install.sql');
    }

    function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $_966293903 = SiteTable::getList(array('filter' => array('ACTIVE' => 'Y')));
        while ($_903582902 = $_966293903->fetch()) {
            $this->unInstallData($_903582902['LID']);
        }
        ModuleManager::unRegisterModule(self::MODULE_ID);
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default');
        if (is_dir($_1543859195 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default/')) {
            if ($_1852328860 = opendir($_1543859195)) {
                while (false !== $_116833994 = readdir($_1852328860)) {
                    if ($_116833994 == '..' || $_116833994 == '.' || !is_dir($_879643787 = $_1543859195 . '/' . $_116833994)) continue;
                    $_1957147707 = opendir($_879643787);
                    while (false !== $_35923262 = readdir($_1957147707)) {
                        if ($_35923262 == '..' || $_35923262 == '.') continue;
                        DeleteDirFilesEx('/bitrix/themes/.default/' . $_116833994 . '/' . $_35923262);
                    }
                    closedir($_1957147707);
                }
                closedir($_1852328860);
            }
        }
        if (is_dir($_1543859195 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($_1852328860 = opendir($_1543859195)) {
                while (false !== $_116833994 = readdir($_1852328860)) {
                    if ($_116833994 == '..' || $_116833994 == '.' || !is_dir($_879643787 = $_1543859195 . '/' . $_116833994)) continue;
                    $_1957147707 = opendir($_879643787);
                    while (false !== $_35923262 = readdir($_1957147707)) {
                        if ($_35923262 == '..' || $_35923262 == '.') continue;
                        DeleteDirFilesEx('/bitrix/components/' . $_116833994 . '/' . $_35923262);
                    }
                    closedir($_1957147707);
                }
                closedir($_1852328860);
            }
        }
        DeleteDirFilesEx('/local/tests/' . self::MODULE_ID . '/');
        DeleteDirFilesEx('/local/templates/.default/components/bitrix/map.google.view/sotbit_regions/');
        DeleteDirFilesEx('/local/templates/.default/components/bitrix/map.google.view/sotbit_regions/');
        return true;
    }

    function UnInstallDB()
    {
        global $DB;
        $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/db/' . mb_strtolower($DB->type) . '/uninstall.sql');
    }

    function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler('sale', 'OnSaleComponentOrderProperties', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnSaleComponentOrderPropertiesHandler');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnEndBufferContent', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnEndBufferContentHandler');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnUserTypeBuildListHandlerHtml');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnProlog', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnPrologHandler');
        EventManager::getInstance()->unRegisterEventHandler('iblock', 'OnIBlockPropertyBuildList', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnIBlockPropertyBuildListHandler');
        EventManager::getInstance()->unRegisterEventHandler('catalog', 'OnGetOptimalPrice', 'main', '\Sotbit\Regions\EventHandlers', 'OnGetOptimalPriceHandler', '/modules/sotbit.regions/lib/eventhandlers.php');
        EventManager::getInstance()->unRegisterEventHandler('sale', 'onSaleDeliveryRestrictionsClassNamesBuildList', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'onSaleDeliveryRestrictionsClassNamesBuildListHandler');
        EventManager::getInstance()->unRegisterEventHandler('sale', 'onSalePaySystemRestrictionsClassNamesBuildList', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'onSalePaySystemRestrictionsClassNamesBuildListHandler');
        EventManager::getInstance()->unRegisterEventHandler('sale', 'OnSaleOrderBeforeSaved', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnSaleOrderBeforeSavedHandler');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnBeforeEventAdd', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnBeforeEventAddHandler');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnBeforeMailSend', 'sotbit.regions', '\Sotbit\Regions\EventHandlers', 'OnBeforeMailSendHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBuildGlobalMenu', self::MODULE_ID, '\Sotbit\Regions\EventHandlers', 'OnBuildGlobalMenuHandler');
        EventManager::getInstance()->unregisterEventHandler('sale', 'OnCondSaleControlBuildList', self::MODULE_ID, '\Sotbit\Regions\EventHandlers', 'OnCondSaleControlBuildListHandler');
        return true;
    }

    function unInstallData($_824762740)
    {
        $_1622258214 = new \CUserTypeEntity;
        $_966293903 = $_1622258214->GetList(array(), array('ENTITY_ID' => 'SOTBIT_REGIONS'));
        while ($_831231980 = $_966293903->Fetch()) {
            $_569462997 = $_1622258214->Delete($_831231980['ID']);
        }
        return true;
    }

    function SotbitRegionsGetAllFiles($_1563539514)
    {
    }

    function SotbitRegionsDeleteFiles($_1682650181)
    {
    }
}