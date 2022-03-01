<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

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
    static private $_1141523686 = null;

    public static function getMenuParent($_1414029350 = '')
    {
        try {
            if (Loader::includeModule('sotbit.missshop')) {
                $_1414029350 = 'global_menu_missshop';
            }
            if (Loader::includeModule('sotbit.mistershop')) {
                $_1414029350 = 'global_menu_mistershop';
            }
            if (Loader::includeModule('sotbit.b2bshop')) {
                $_1414029350 = 'global_menu_b2bshop';
            }
            if (Loader::includeModule('sotbit.origami')) {
                $_1414029350 = 'global_menu_sotbit';
            }
            if (!$_1414029350 || !is_string($_1414029350)) {
                throw new SystemException('Cannt find menu parent');
            }
            return $_1414029350;
        } catch (SystemException $_120750598) {
            echo $_120750598->getMessage();
        }
    }

    public static function genCodeVariable($_1303777229 = '')
    {
        try {
            $_364962136 = self::getUserTypeFields();
            if ($_364962136[$_1303777229]['USER_TYPE_ID'] == 'file') {
                return false;
            }
            if (!$_1303777229 || !is_string($_1303777229)) {
                throw new SystemException('Code isnt string');
            }
            return str_replace('#CODE#', $_1303777229, self::mask);
        } catch (SystemException $_120750598) {
            echo $_120750598->getMessage();
        }
    }

    public static function getUserTypeFields()
    {
        $_1841218064 = [];
        $_364962136 = \CUserTypeEntity::GetList(['FIELD_NAME' => 'ASC'], ['ENTITY_ID' => self::entityId]);
        while ($_661905157 = $_364962136->Fetch()) {
            $_1841218064[$_661905157['FIELD_NAME']] = $_661905157;
        }
        return $_1841218064;
    }

    public static function getTags($_162395352 = array())
    {
        $_1849893943 = array();
        if (!$_162395352) {
            $_162395352 = array_keys(self::getSites());
        }
        $_1849893943[0] = array('CODE' => 'CODE', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_CODE'));
        $_1849893943[1] = array('CODE' => 'NAME', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_NAME'));
        $_1849893943[2] = array('CODE' => 'SORT', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_SORT'));
        $_1849893943[3] = array('CODE' => 'PRICE_CODE', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_PRICE_CODE'));
        $_1849893943[4] = array('CODE' => 'STORE', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_STORE'));
        $_1849893943[5] = array('CODE' => 'COUNTER', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_COUNTER'));
        $_1849893943[6] = array('CODE' => 'MAP_YANDEX', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_MAP_YANDEX'));
        $_1849893943[7] = array('CODE' => 'MAP_GOOGLE', 'NAME' => Loc::getMessage(\SotbitRegions::moduleId . '_MAP_GOOGLE'));
        $_1037817112 = 8;
        foreach ($_162395352 as $_1068141599) {
            $_364962136 = self::getUserTypeFields();
            foreach ($_364962136 as $_661905157) {
                $_661905157 = \CUserTypeEntity::GetByID($_661905157['ID']);
                if ($_661905157['USER_TYPE_ID'] == 'file') {
                    $_1849893943[$_1037817112++] = array('CODE' => $_661905157['FIELD_NAME'], 'NAME' => $_661905157['LIST_COLUMN_LABEL'][LANGUAGE_ID] . Loc::getMessage(\SotbitRegions::moduleId . '_FILE'));
                } else {
                    $_1849893943[$_1037817112++] = array('CODE' => $_661905157['FIELD_NAME'], 'NAME' => $_661905157['LIST_COLUMN_LABEL'][LANGUAGE_ID]);
                }
            }
        }
        return $_1849893943;
    }

    public static function getSites()
    {
        $_162395352 = array();
        try {
            $_1639465744 = \Bitrix\Main\SiteTable::getList(array('select' => array('SITE_NAME', 'LID'), 'filter' => array('ACTIVE' => 'Y'),));
            while ($_1068141599 = $_1639465744->fetch()) {
                $_162395352[$_1068141599['LID']] = $_1068141599['SITE_NAME'];
            }
            if (!is_array($_162395352) || count($_162395352) == 0) {
                throw new SystemException('Cannt get sites');
            }
        } catch (SystemException $_120750598) {
            echo $_120750598->getMessage();
        }
        return $_162395352;
    }
}