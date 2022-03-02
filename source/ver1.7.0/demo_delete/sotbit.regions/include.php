<?php
use Bitrix\Main\SystemException, Bitrix\Main\Loader, Bitrix\Main\Localization\Loc;

class SotbitRegions
{
    const moduleId = 'sotbit.regions';
    const regionsPath = 'sotbit_regions.php';
    const regionPath = 'sotbit_regions_edit.php';
    const settingsPath = 'sotbit_regions_settings.php';
    const sitemapPath = 'sotbit_regions_seofiles.php';
    const regionImport = 'sotbit_regions_import.php';
    const regionExport = 'sotbit_regions_export.php';
    const mask = '#SOTBIT_REGIONS_#CODE##';
    const entityId = 'SOTBIT_REGIONS';
    static private $_1578505311 = null;

    public static function isDemoEnd()
    {
        if (is_null(self::$_1578505311)) {
            self::__1118618289();
        }
        if (self::$_1578505311 == 0 || self::$_1578505311 == 3) {
            return true;
        } else {
            return false;
        }
    }

    public static function getDemo()
    {
        if (is_null(self::$_1578505311)) {
            self::__1118618289();
        }
        return self::$_1578505311;
    }

    public static function getSites()
    {
        $_1411477415 = array();
        try {
            $_1180077449 = \Bitrix\Main\SiteTable::getList(array('select' => array('SITE_NAME', 'LID'), 'filter' => array('ACTIVE' => 'Y'),));
            while ($_1941854286 = $_1180077449->fetch()) {
                $_1411477415[$_1941854286['LID']] = $_1941854286['SITE_NAME'];
            }
            if (!is_array($_1411477415) || count($_1411477415) == 0) {
                throw new SystemException('Cannt get sites');
            }
        } catch (SystemException $_53135731) {
            echo $_53135731->getMessage();
        }
        return $_1411477415;
    }

    public static function getMenuParent($_2098065491 = '')
    {
        try {
            if (Loader::includeModule('sotbit.missshop')) {
                $_2098065491 = 'global_menu_missshop';
            }
            if (Loader::includeModule('sotbit.mistershop')) {
                $_2098065491 = 'global_menu_mistershop';
            }
            if (Loader::includeModule('sotbit.b2bshop')) {
                $_2098065491 = 'global_menu_b2bshop';
            }
            if (Loader::includeModule('sotbit.origami')) {
                $_2098065491 = 'global_menu_sotbit';
            }
            if (!$_2098065491 || !is_string($_2098065491)) {
                throw new SystemException('Cannt find menu parent');
            }
            return $_2098065491;
        } catch (SystemException $_53135731) {
            echo $_53135731->getMessage();
        }
    }

    public static function genCodeVariable($_840086782 = '')
    {
        try {
            $_1221628771 = self::getUserTypeFields();
            if ($_1221628771[$_840086782]['USER_TYPE_ID'] == 'file') {
                return false;
            }
            if (!$_840086782 || !is_string($_840086782)) {
                throw new SystemException('Code isnt string');
            }
            return str_replace('#CODE#', $_840086782, self::mask);
        } catch (SystemException $_53135731) {
            echo $_53135731->getMessage();
        }
    }

    public static function getTags($_1411477415 = array())
    {
        $_337095991 = array();
        if (!$_1411477415) {
            $_1411477415 = array_keys(self::getSites());
        }
        $_337095991[0] = array('CODE' => 'CODE', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_CODE'));
        $_337095991[1] = array('CODE' => 'NAME', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_NAME'));
        $_337095991[2] = array('CODE' => 'SORT', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_SORT'));
        $_337095991[3] = array('CODE' => 'PRICE_CODE', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_PRICE_CODE'));
        $_337095991[4] = array('CODE' => 'STORE', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_STORE'));
        $_337095991[5] = array('CODE' => 'COUNTER', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_COUNTER'));
        $_337095991[6] = array('CODE' => 'MAP_YANDEX', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_MAP_YANDEX'));
        $_337095991[7] = array('CODE' => 'MAP_GOOGLE', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_MAP_GOOGLE'));
        $_1207866963 = 8;
        foreach ($_1411477415 as $_1941854286) {
            $_1221628771 = self::getUserTypeFields();
            foreach ($_1221628771 as $_438270437) {
                $_438270437 = \CUserTypeEntity::GetByID($_438270437['ID']);
                if ($_438270437['USER_TYPE_ID'] == 'file') {
                    $_337095991[$_1207866963++] = array('CODE' => $_438270437['FIELD_NAME'], 'NAME' => $_438270437['LIST_COLUMN_LABEL'][LANGUAGE_ID] . Loc::getMessage(\SotbitRegions::moduleId . '_FILE'));
                } else {
                    $_337095991[$_1207866963++] = array('CODE' => $_438270437['FIELD_NAME'], 'NAME' => $_438270437['LIST_COLUMN_LABEL'][LANGUAGE_ID]);
                }
            }
        }
        return $_337095991;
    }

    public static function getUserTypeFields()
    {
        $_879685939 = [];
        $_1221628771 = \CUserTypeEntity::GetList(['FIELD_NAME' => 'ASC'], ['ENTITY_ID' => self::entityId]);
        while ($_438270437 = $_1221628771->Fetch()) {
            $_879685939[$_438270437['FIELD_NAME']] = $_438270437;
        }
        return $_879685939;
    }

    private static function __1118618289()
    {
        self::$_1578505311 = \Bitrix\Main\Loader::includeSharewareModule(self::moduleId);
    }
}