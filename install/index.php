<?php

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Service\GeoIp;
use Bitrix\Main\Service\GeoIp\EO_Handler;
use Bitrix\Main\SiteTable;
use Bitrix\Sale\Location\TypeTable;
use Kit\Regions\SypexGeo\SypexGeoUpdater;

Loc::loadMessages(__FILE__);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client_partner.php');
require __DIR__ . '/../lib/sypexgeo/sypexgeolocal.php';
require __DIR__ . '/../lib/sypexgeo/sypexgeoupdater.php';

class kit_regions extends CModule
{
    const MODULE_ID = 'kit.regions';
    var $MODULE_ID = 'kit.regions';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $_1934944684 = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage(self::MODULE_ID . '_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage(self::MODULE_ID . '_MODULE_DESC');
        $this->PARTNER_NAME = GetMessage('kit.regions_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('kit.regions_PARTNER_URI');
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
            $APPLICATION->IncludeAdminFile(GetMessage('INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/kit.regions/install/step.php');
        }
        SypexGeoUpdater::setAgent();
    }

    function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler('main', 'onMainGeoIpHandlersBuildList', 'kit.regions', '\\' . \Kit\Regions\EventHandlers::class, "addGeoIpServes",);
//        EventManager::getInstance()->registerEventHandler('main', 'onMainGeoIpHandlersBuildList', 'kit.regions', '\\Kit\Regions\EventHandlers::class, "addGeoIpServes"',);
        EventManager::getInstance()->registerEventHandler('sale', 'OnSaleComponentOrderProperties', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnSaleComponentOrderPropertiesHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnEndBufferContent', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnEndBufferContentHandler', 999);
        EventManager::getInstance()->registerEventHandler('main', 'OnUserTypeBuildList', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnUserTypeBuildListHandlerHtml');
        EventManager::getInstance()->registerEventHandler('main', 'OnProlog', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnPrologHandler');
        EventManager::getInstance()->registerEventHandler('iblock', 'OnIBlockPropertyBuildList', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnIBlockPropertyBuildListHandler');
        EventManager::getInstance()->registerEventHandler('catalog', 'OnGetOptimalPrice', 'main', '\Kit\Regions\EventHandlers', 'OnGetOptimalPriceHandler', 100, '/modules/kit.regions/lib/eventhandlers.php');
        EventManager::getInstance()->registerEventHandler('sale', 'onSaleDeliveryRestrictionsClassNamesBuildList', 'kit.regions', '\Kit\Regions\EventHandlers', 'onSaleDeliveryRestrictionsClassNamesBuildListHandler');
        EventManager::getInstance()->registerEventHandler('sale', 'onSalePaySystemRestrictionsClassNamesBuildList', 'kit.regions', '\Kit\Regions\EventHandlers', 'onSalePaySystemRestrictionsClassNamesBuildListHandler');
        EventManager::getInstance()->registerEventHandler('sale', 'OnSaleOrderBeforeSaved', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnSaleOrderBeforeSavedHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeEventAdd', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnBeforeEventAddHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeMailSend', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnBeforeMailSendHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBuildGlobalMenu', self::MODULE_ID, '\Kit\Regions\EventHandlers', 'OnBuildGlobalMenuHandler');
        EventManager::getInstance()->registerEventHandler('sale', 'OnCondSaleControlBuildList', self::MODULE_ID, '\Kit\Regions\EventHandlers', 'OnCondSaleControlBuildListHandler');
        $_1516249333 = SiteTable::getList(array('filter' => array('ACTIVE' => 'Y')));
        while ($_1398202006 = $_1516249333->fetch()) {
            $this->KitRegionsInstallData($_1398202006['LID']);
            $this->KitRegionsSetSettings($_1398202006['LID']);
        }
        return true;
    }

    function KitRegionsInstallData($_1260573971 = '')
    {
        $this->KitRegionsInstallProperties($_1260573971);
        $this->KitRegionsInstallDomains($_1260573971);
        $this->KitRegionsInstallFavorites($_1260573971);
        return true;
    }

    function KitRegionsInstallProperties($_1260573971)
    {
        $_1149498196 = array();
        $_1937458835 = CLanguage::GetList($_1205831262, $_1775081192, array());
        while ($_1402893842 = $_1516249333 = $_1937458835->Fetch()) {
            $_1149498196[] = htmlspecialcharsbx($_1402893842['LID']);
        }
        $_135703835 = new \CUserTypeEntity;
        $_1649592066 = array('ENTITY_ID' => 'KIT_REGIONS', 'SORT' => 100, 'MULTIPLE' => 'N', 'MANDATORY' => 'N', 'SHOW_FILTER' => 'N', 'SHOW_IN_LIST' => 'Y', 'EDIT_IN_LIST' => 'Y', 'IS_SEARCHABLE' => 'N', 'SETTINGS' => array(), 'EDIT_FORM_LABEL' => array(), 'LIST_COLUMN_LABEL' => array(), 'LIST_FILTER_LABEL' => array(), 'ERROR_MESSAGE' => array(), 'HELP_MESSAGE' => array(),);
        $_1368976908 = array('PHONE' => array('FIELD_NAME' => 'UF_PHONE', 'USER_TYPE_ID' => 'string', 'MULTIPLE' => 'Y'), 'ADDRESS' => array('FIELD_NAME' => 'UF_ADDRESS', 'USER_TYPE_ID' => 'html',), 'EMAIL' => array('FIELD_NAME' => 'UF_EMAIL', 'USER_TYPE_ID' => 'string', 'MULTIPLE' => 'Y'), 'ROBOTS' => array('FIELD_NAME' => 'UF_ROBOTS', 'USER_TYPE_ID' => 'html',),);
        foreach ($_1368976908 as $_1723785441 => $_1684220390) {
            $_1145877735 = array_merge($_1649592066, $_1684220390);
            foreach ($_1149498196 as $_434315318) {
                $_1145877735['EDIT_FORM_LABEL'][$_434315318] = Loc::getMessage('kit.regions_PROP_' . $_1723785441);
                $_1145877735['LIST_COLUMN_LABEL'][$_434315318] = Loc::getMessage('kit.regions_PROP_' . $_1723785441);
                $_1145877735['LIST_FILTER_LABEL'][$_434315318] = Loc::getMessage('kit.regions_PROP_' . $_1723785441);
                $_1145877735['ERROR_MESSAGE'][$_434315318] = Loc::getMessage('kit.regions_PROP_' . $_1723785441);
                $_1145877735['HELP_MESSAGE'][$_434315318] = Loc::getMessage('kit.regions_PROP_' . $_1723785441);
            }
            $_904595014 = $_135703835->Add($_1145877735);
        }
    }

    function KitRegionsInstallDomains($_1260573971)
    {
        $_79589753 = array();
        $_2110945885 = array();
        if (Loader::includeModule('catalog')) {
            $_1516249333 = \CCatalogGroup::GetList(array(), array('ACTIVE' => 'Y'));
            while ($_788247857 = $_1516249333->Fetch()) {
                $_79589753[] = $_788247857['NAME'];
            }
            $_1516249333 = \CCatalogStore::GetList(array(), array('ISSUING_CENTER' => 'Y', 'ACTIVE' => 'Y'), false, false, array('ID'));
            while ($_2008148986 = $_1516249333->Fetch()) {
                $_2110945885[] = $_2008148986['ID'];
            }
        }
        $_957705035 = array('', 'spb', 'sochi', 'pyatigorsk', 'voronezh', 'krasnodar', 'samara', 'rostov', 'ufa', 'kaluga', 'kazan', 'stavropol', 'nn');
        $_680918118 = Bitrix\Main\Application::getInstance()->getContext();
        $_544924460 = $_680918118->getServer();
        $_1860535200 = $_544924460->getServerName();
        $_1253365073 = (!empty($_SERVER['HTTPS']) && 'off' !== mb_strtolower($_SERVER['HTTPS'])) ? 'https://' : 'http://';
        global $DB;
        foreach ($_957705035 as $_52041450 => $_69734081) {
            if (!empty($_69734081)) {
                $_1667311065 = $_69734081 . '.';
            } else {
                $_1667311065 = $_69734081;
            }
            $_1852526104 = $_1253365073 . $_1667311065 . $_1860535200;



            $_1944572090 = array('CODE' => $_1852526104, 'NAME' => Loc::getMessage('kit.regions_DOMEN_' . $_69734081), 'SORT' => 100, 'PRICE_CODE' => $_79589753, 'STORE' => $_2110945885, 'SITE_ID' => [$_1260573971]);
            if ($_52041450 == 0) $_1887043063 = '\'Y\''; else $_1887043063 = 'NULL';


            $DB->Query("INSERT INTO `kit_regions` VALUES (NULL,'" . $_1944572090["CODE"] . "', '" . $_1944572090["NAME"] . "', 100, '" . serialize($_1944572090["SITE_ID"]) . "', '" . serialize($_1944572090["PRICE_CODE"]) . "', '" . serialize($_1944572090["STORE"]) . "', NULL, NULL, NULL, NULL, NULL, NULL, " . $_1887043063 . ");");



            $_389040470 = intval($DB->LastID());
            if ($_389040470 > 0) {
                $_692322523 = '+7495';
                switch ($_69734081) {
                    case 'spb':
                        $_692322523 = '+7 812 ';
                        break;
                    case 'sochi':
                        $_692322523 = '+7 8622 ';
                        break;
                    case 'pyatigorsk':
                        $_692322523 = '+7 8793 ';
                        break;
                    case 'voronezh':
                        $_692322523 = '+7 4732 ';
                        break;
                    case 'krasnodar':
                        $_692322523 = '+7 861 ';
                        break;
                    case 'samara':
                        $_692322523 = '+7 846 ';
                        break;
                    case 'rostov':
                        $_692322523 = '+7 863 ';
                        break;
                    case 'ufa':
                        $_692322523 = '+7 347 ';
                        break;
                    case 'kaluga':
                        $_692322523 = '+7 4842 ';
                        break;
                    case 'kazan':
                        $_692322523 = '+7 843 ';
                        break;
                    case 'stavropol':
                        $_692322523 = '+7 8652 ';
                        break;
                    case 'nn':
                        $_692322523 = '+7 831 ';
                        break;
                }
                if (mb_strlen($_692322523) == 9) {
                    $_1700836254 = array($_692322523 . '111-11-11', $_692322523 . '222-22-22');
                } else {
                    $_1700836254 = array($_692322523 . '11-11-11', $_692322523 . '22-22-22');
                }
                $DB->Query("INSERT INTO `kit_regions_fields` VALUES (NULL," . $_389040470 . ", 'UF_PHONE', '" . serialize($_1700836254) . "');");
                $DB->Query("INSERT INTO `kit_regions_fields` VALUES (NULL," . $_389040470 . ", 'UF_ADDRESS', '" . Loc::getMessage("kit.regions_ADDRESS", array("#HOME#" => rand(1, 50))) . "');");
                $DB->Query("INSERT INTO `kit_regions_fields` VALUES (NULL," . $_389040470 . ", 'UF_EMAIL', '" . serialize(["sales@" . $_1667311065 . $_1860535200]) . "');");
                $DB->Query("INSERT INTO `kit_regions_fields` VALUES (NULL," . $_389040470 . ", 'UF_ROBOTS', '');");


                if (Loader::includeModule('sale')) {
                    $_1822669924 = \Bitrix\Sale\Location\LocationTable::getList(['filter' => ['=NAME.NAME' => Loc::getMessage('kit.regions_LOCATION_' . $_69734081), '=NAME.LANGUAGE_ID' => LANGUAGE_ID,], 'select' => ['*', 'NAME.*',], 'cache' => ['ttl' => 36000000,],])->fetch();
                    if ($_1822669924['ID'] > 0) {
                        $DB->Query("INSERT INTO `kit_regions_locations` VALUES (NULL," . $_389040470 . "," . $_1822669924["ID"] . ");");
                    }
                    if ($_69734081 == '') {
                        $_1822669924 = \Bitrix\Sale\Location\LocationTable::getList(['filter' => ['=NAME.NAME' => Loc::getMessage('kit.regions_LOCATION_MOSCOW'), '=NAME.LANGUAGE_ID' => LANGUAGE_ID,], 'select' => ['*', 'NAME.*',], 'cache' => ['ttl' => 36000000,],])->fetch();
                    }
                    if ($_1822669924['ID'] > 0) {
                        $DB->Query("INSERT INTO `kit_regions_locations` VALUES (NULL," . $_389040470 . "," . $_1822669924["ID"] . ");");
                    }
                }
            }
        }
    }

    function KitRegionsInstallFavorites($_1260573971)
    {
        if (Loader::includeModule('sale')) {
            $_820219324 = ['MOSCOW', 'KALUGA', 'KAZAN', 'KRASNODAR', 'NN', 'PYATIGORSK', 'ROSTOV_NA_DONY', 'SAMARA', 'SOCHI', 'SP', 'STAVROPOL', 'UFA', 'VORONEG',];
            foreach ($_820219324 as $_915952566) {
                $_1822669924 = \Bitrix\Sale\Location\LocationTable::getList(['filter' => ['=NAME.NAME' => Loc::getMessage('kit.regions_FAVORITE_' . $_915952566), '=NAME.LANGUAGE_ID' => LANGUAGE_ID,], 'select' => ['*', 'NAME.*',], 'cache' => ['ttl' => 36000000,],])->fetch();
                if ($_1822669924['CODE']) {
                    $_828804991 = \Bitrix\Sale\Location\DefaultSiteTable::getList(['filter' => ['LOCATION_CODE' => $_1822669924['CODE'], 'SITE_ID' => $_1260573971]])->getSelectedRowsCount();
                    if ($_828804991 == 0) \Bitrix\Sale\Location\DefaultSiteTable::add(['SORT' => 100, 'LOCATION_CODE' => $_1822669924['CODE'], 'SITE_ID' => $_1260573971]);
                }
            }
        }
    }

    function KitRegionsSetSettings($_1260573971)
    {
        global $DB;
        $DB->Query("INSERT INTO `kit_regions_options` VALUES ('SINGLE_DOMAIN','Y', '" . $_1260573971 . "');");
        if (Loader::includeModule('statistic')) {
            $DB->Query("INSERT INTO `kit_regions_options` VALUES ('FIND_USER_METHOD','statistic', '" . $_1260573971 . "');");
        } elseif (function_exists('curl_version')) {
            $DB->Query("INSERT INTO `kit_regions_options` VALUES ('FIND_USER_METHOD','ipgeobase', '" . $_1260573971 . "');");
        } else {
            $DB->Query("INSERT INTO `kit_regions_options` VALUES ('FIND_USER_METHOD','geoip','" . $_1260573971 . "');");
        }
        $DB->Query("INSERT INTO `kit_regions_options` VALUES ('INSERT_SALE_LOCATION','N', '" . $_1260573971 . "');");
        $DB->Query("INSERT INTO `kit_regions_options` VALUES ('MULTIPLE_DELIMITER',', ', '" . $_1260573971 . "');");
        $_1396366420 = 5;
        if (Loader::includeModule('sale')) {
            $_1332076988 = TypeTable::getList(['select' => ['ID'], 'filter' => ['CODE' => 'CITY']])->fetch();
            if (!empty($_1332076988['ID'])) $_1396366420 = $_1332076988['ID'];
        }
        $DB->Query("INSERT INTO `kit_regions_options` VALUES ('LOCATION_TYPE', '" . $_1396366420 . "', '" . $_1260573971 . "');");
    }

    function InstallFiles($_1510988695 = array())
    {
        if (Loader::includeModule('kit.origami')) {
            CopyDirFiles(__DIR__ . '/origami_templates', Application::getDocumentRoot(), true, true);
        }
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true);
        if (is_dir($_212967155 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default')) {
            if ($_348137096 = opendir($_212967155)) {
                while (false !== $_1271387824 = readdir($_348137096)) {
                    if ($_1271387824 == '..' || $_1271387824 == '.') continue;
                    CopyDirFiles($_212967155 . '/' . $_1271387824, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/' . $_1271387824, $_1053155751 = True, $_1593034047 = True);
                }
                closedir($_348137096);
            }
        }
        if (is_dir($_212967155 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($_348137096 = opendir($_212967155)) {
                while (false !== $_1271387824 = readdir($_348137096)) {
                    if ($_1271387824 == '..' || $_1271387824 == '.') continue;
                    CopyDirFiles($_212967155 . '/' . $_1271387824, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $_1271387824, $_1053155751 = True, $_1593034047 = True);
                }
                closedir($_348137096);
            }
        }
        if (is_dir($_212967155 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/local')) {
            if ($_348137096 = opendir($_212967155)) {
                while (false !== $_1271387824 = readdir($_348137096)) {
                    if ($_1271387824 == '..' || $_1271387824 == '.') continue;
                    CopyDirFiles($_212967155 . '/' . $_1271387824, $_SERVER['DOCUMENT_ROOT'] . '/local/' . $_1271387824, $_1053155751 = True, $_1593034047 = True);
                }
                closedir($_348137096);
            }
        }
        return true;
    }

    function installDB()
    {
        global $DB;
        $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/db/' . mb_strtolower($DB->type) . '/install.sql');
        $_1057636470 = GeoIp\HandlerTable::add(['SORT' => 105, 'ACTIVE' => 'Y', 'CLASS_NAME' => '\\' . \Kit\Regions\SypexGeo\SypexGeoLocal::class, "CONFIG" => "",]);
//        $_1057636470 = GeoIp\HandlerTable::add(['SORT' => 105,  'ACTIVE' => 'Y', 'CLASS_NAME' => '\\Kit\Regions\SypexGeo\SypexGeoLocal::class, "CONFIG" => ""',]);
    }

    function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $_1516249333 = SiteTable::getList(array('filter' => array('ACTIVE' => 'Y')));
        while ($_1398202006 = $_1516249333->fetch()) {
            $this->unInstallData($_1398202006['LID']);
        }
        ModuleManager::unRegisterModule(self::MODULE_ID);
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default');
        \Bitrix\Main\IO\Directory::deleteDirectory(Application::getDocumentRoot() . '/local/templates/.default/components/kit/regions.choose');
        if (is_dir($_212967155 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default/')) {
            if ($_348137096 = opendir($_212967155)) {
                while (false !== $_1271387824 = readdir($_348137096)) {
                    if ($_1271387824 == '..' || $_1271387824 == '.' || !is_dir($_690581297 = $_212967155 . '/' . $_1271387824)) continue;
                    $_2029615420 = opendir($_690581297);
                    while (false !== $_460140899 = readdir($_2029615420)) {
                        if ($_460140899 == '..' || $_460140899 == '.') continue;
                        DeleteDirFilesEx('/bitrix/themes/.default/' . $_1271387824 . '/' . $_460140899);
                    }
                    closedir($_2029615420);
                }
                closedir($_348137096);
            }
        }
        if (is_dir($_212967155 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($_348137096 = opendir($_212967155)) {
                while (false !== $_1271387824 = readdir($_348137096)) {
                    if ($_1271387824 == '..' || $_1271387824 == '.' || !is_dir($_690581297 = $_212967155 . '/' . $_1271387824)) continue;
                    $_2029615420 = opendir($_690581297);
                    while (false !== $_460140899 = readdir($_2029615420)) {
                        if ($_460140899 == '..' || $_460140899 == '.') continue;
                        DeleteDirFilesEx('/bitrix/components/' . $_1271387824 . '/' . $_460140899);
                    }
                    closedir($_2029615420);
                }
                closedir($_348137096);
            }
        }
        DeleteDirFilesEx('/local/tests/' . self::MODULE_ID . '/');
        DeleteDirFilesEx('/local/templates/.default/components/bitrix/map.google.view/kit_regions/');
        DeleteDirFilesEx('/local/templates/.default/components/bitrix/map.google.view/kit_regions/');
        return true;
    }

    function UnInstallDB()
    {
        global $DB;
        $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/db/' . mb_strtolower($DB->type) . '/uninstall.sql');
        $_2082569957 = GeoIp\HandlerTable::query()->where('CLASS_NAME', '\\' . \Kit\Regions\SypexGeo\SypexGeoLocal::class)->fetchObject();
//        $_2082569957 = GeoIp\HandlerTable::query()->where('CLASS_NAME', '\\Kit\Regions\SypexGeo\SypexGeoLocal::class)->fetchObject();
        if ($_2082569957 instanceof EO_Handler) {
            $_2082569957->delete();
        }
        unlink(__DIR__ . '/../lib/sypexgeo/SxGeoCity.dat');
        unlink(__DIR__ . '/../lib/sypexgeo/SxGeo.upd');
        unlink(__DIR__ . '/../lib/sypexgeo/sypexGeoUpdate.log');
        SypexGeoUpdater::removeAgent();
    }

    function UnInstallEvents()
    {
        EventManager::getInstance()->registerEventHandler('main', 'onMainGeoIpHandlersBuildList', 'kit.regions', '\\' . \Kit\Regions\EventHandlers::class, "addGeoIpServes",);
//        EventManager::getInstance()->registerEventHandler('main', 'onMainGeoIpHandlersBuildList', 'kit.regions', '\\Kit\Regions\EventHandlers::class, "addGeoIpServes"',);
        EventManager::getInstance()->unRegisterEventHandler('sale', 'OnSaleComponentOrderProperties', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnSaleComponentOrderPropertiesHandler');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnEndBufferContent', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnEndBufferContentHandler');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnUserTypeBuildListHandlerHtml');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnProlog', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnPrologHandler');
        EventManager::getInstance()->unRegisterEventHandler('iblock', 'OnIBlockPropertyBuildList', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnIBlockPropertyBuildListHandler');
        EventManager::getInstance()->unRegisterEventHandler('catalog', 'OnGetOptimalPrice', 'main', '\Kit\Regions\EventHandlers', 'OnGetOptimalPriceHandler', '/modules/kit.regions/lib/eventhandlers.php');
        EventManager::getInstance()->unRegisterEventHandler('sale', 'onSaleDeliveryRestrictionsClassNamesBuildList', 'kit.regions', '\Kit\Regions\EventHandlers', 'onSaleDeliveryRestrictionsClassNamesBuildListHandler');
        EventManager::getInstance()->unRegisterEventHandler('sale', 'onSalePaySystemRestrictionsClassNamesBuildList', 'kit.regions', '\Kit\Regions\EventHandlers', 'onSalePaySystemRestrictionsClassNamesBuildListHandler');
        EventManager::getInstance()->unRegisterEventHandler('sale', 'OnSaleOrderBeforeSaved', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnSaleOrderBeforeSavedHandler');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnBeforeEventAdd', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnBeforeEventAddHandler');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnBeforeMailSend', 'kit.regions', '\Kit\Regions\EventHandlers', 'OnBeforeMailSendHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBuildGlobalMenu', self::MODULE_ID, '\Kit\Regions\EventHandlers', 'OnBuildGlobalMenuHandler');
        EventManager::getInstance()->unregisterEventHandler('sale', 'OnCondSaleControlBuildList', self::MODULE_ID, '\Kit\Regions\EventHandlers', 'OnCondSaleControlBuildListHandler');
        return true;
    }

    function unInstallData($_1260573971)
    {
        $_135703835 = new \CUserTypeEntity;
        $_1516249333 = $_135703835->GetList(array(), array('ENTITY_ID' => 'KIT_REGIONS'));
        while ($_500713783 = $_1516249333->Fetch()) {
            $_1057636470 = $_135703835->Delete($_500713783['ID']);
        }
        return true;
    }

    function KitRegionsGetAllFiles($_1602241508)
    {
    }

    function KitRegionsDeleteFiles($_671367095)
    {
    }
}