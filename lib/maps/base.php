<?

namespace Kit\Regions\Maps;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;

/**
 * Class Base
 *
 * @package Kit\Regions\Maps
 * Date: 12.11.2019
 */
class Base
{
    const DEFAULT_SCALE = 12;
    const DEFAULT_HEIGHT = "400";
    const DEFAULT_WIDTH = "100%";
    const DEFAULT_USER_FIELDS_TYPE = ['html', 'string', 'integer', 'double'];
    const DEFAULT_USER_FIELDS = ['NAME', 'UF_ADDRESS', 'UF_PHONE', 'UF_EMAIL'];

    /**
     * Get maps type
     *
     * @param null $code
     *
     * @return array|mixed
     */
    public static function getMapsType($code = null)
    {
        $types = [
            'yandex' => Loc::getMessage(\KitRegions::moduleId.'_MAPS_TYPE_YANDEX'),
            'google' => Loc::getMessage(\KitRegions::moduleId.'_MAPS_TYPE_GOOGLE'),
        ];

        if ($code && in_array($code, array_keys($types))) {
            return $types[$code];
        } else {
            return $types;
        }
    }

    /**
     * Get all scale
     *
     * @return array
     */
    public static function getScale()
    {
        return range(0, 17);
    }

    /**
     * Output fields for maps
     *
     * @return array
     */
    public static function getUserFields()
    {
        $mapMarkerFields = [
            'NAME' => GetMessage(\KitRegions::moduleId.'_MAPS_MARKER_FIELDS_NAME'),
            'CODE' => GetMessage(\KitRegions::moduleId.'_MAPS_MARKER_FIELDS_CODE'),
        ];
        $arFields = \KitRegions::getUserTypeFields();
        foreach ($arFields as $userField) {
            if (!in_array($userField['USER_TYPE_ID'], self::DEFAULT_USER_FIELDS_TYPE)) {
                continue;
            }
            $lang = \CUserTypeEntity::GetByID($userField['ID']);
            $mapMarkerFields[$userField['FIELD_NAME']] = $lang['EDIT_FORM_LABEL']['ru'];
        }

        return $mapMarkerFields;
    }

    /**
     * Checking a variable for serialize
     *
     * @param      $value
     * @param null $result
     *
     * @return bool
     */
    function is_serialized($value, &$result = null)
    {
        // Bit of a give away this one
        if (!is_string($value)) {
            return false;
        }
        // Serialized false, return true. unserialize() returns false on an
        // invalid string or it could return false if the string is serialized
        // false, eliminate that possibility.
        if ($value === 'b:0;') {
            $result = false;

            return true;
        }
        $length = mb_strlen($value);
        $end = '';
        switch ($value[0]) {
            case 's':
                if ($value[$length - 2] !== '"') {
                    return false;
                }
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';
                if ($value[1] !== ':') {
                    return false;
                }
                switch ($value[2]) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        break;
                    default:
                        return false;
                }
            case 'N':
                $end .= ';';
                if ($value[$length - 1] !== $end[0]) {
                    return false;
                }
                break;
            default:
                return false;
        }
        if (($result = @unserialize($value)) === false) {
            $result = null;

            return false;
        }

        return true;
    }
}