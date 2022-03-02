<?

namespace Kit\Regions\Location;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Kit\Regions\Internals\FieldsTable;
use Kit\Regions\Region;
use Kit\Regions\Config\Option;
use Kit\Regions\Internals\RegionsTable;

/**
 * Class Location
 *
 * @package Kit\Regions
 */
class Domain
{
    /**
     * @var null|string
     */
    public static $props = null;
    public static $autoDef = false;
    public static $withLocation = true;
    public static $singleDomain = false;

    /**
     * @var Variables
     */
    private $variables;


    /**
     * Domain constructor.
     *
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    public function __construct()
    {
        if (!\KitRegions::isDemoEnd())
        {
            self::setLocation();
            $this->variables = Variables::getInstance();
        }
    }

    /**
     * @return null|array
     */
    public function getProps()
    {
        return self::$props;
    }

    public function getProp($code = '')
    {
        return self::$props[$code];
    }

    /**
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    private static function setLocation()
    {
        self::$withLocation = (Option::get('MODE_LOCATION', SITE_ID) == 'Y') ? true : false;
        self::$singleDomain = (Option::get('SINGLE_DOMAIN', SITE_ID) == 'Y') ? true : false;

        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        //unset($_SESSION['KIT_REGIONS']);

        if(isset($_SESSION['KIT_REGIONS']['LOCATION']['ID']) && !self::$withLocation)
        {
            unset($_SESSION['KIT_REGIONS']);
            self::$props = null;
        }

        if(isset($_SESSION['KIT_REGIONS']) && !isset($_SESSION['KIT_REGIONS']['LOCATION']['ID']) && self::$withLocation )
        {
            unset($_SESSION['KIT_REGIONS']);
            self::$props = null;
        }

        if(isset($_COOKIE['kit_regions_location_id']) && !self::$withLocation)
        {
            unset($_COOKIE['kit_regions_location_id']);
            //setcookie('kit_regions_location_id', "", time()-3600);
            unset($_COOKIE['kit_regions_id']);
            //setcookie('kit_regions_id', "", time()-3600);

            if(isset($_COOKIE['kit_regions_city_choosed']))
            {
                unset($_COOKIE['kit_regions_city_choosed']);
                //setcookie('kit_regions_city_choosed', "", time()-3600);
            }
            self::$props = null;
        }

        if(isset($_COOKIE['kit_regions_id']) && !isset($_COOKIE['kit_regions_location_id']) && self::$withLocation)
        {
            unset($_COOKIE['kit_regions_id']);
            //setcookie('kit_regions_id', "", time()-3600);

            if(isset($_COOKIE['kit_regions_city_choosed']))
            {
                unset($_COOKIE['kit_regions_city_choosed']);
                //setcookie('kit_regions_city_choosed', "", time()-3600);
            }
            self::$props = null;
        }

        if ($_SESSION['KIT_REGIONS']['LOCATION']['ID'] != $_COOKIE['kit_regions_location_id'] && $_COOKIE['kit_regions_location_id'] > 0)
        {
            unset($_SESSION['KIT_REGIONS']);
            self::$props = null;
        }
        if ($_SESSION['KIT_REGIONS']['ID'] != $_COOKIE['kit_regions_id'] && $_COOKIE['kit_regions_id'] > 0)
        {
            unset($_SESSION['KIT_REGIONS']);
            self::$props = null;
        }
        if (isset($_SESSION['KIT_REGIONS']))
        {
            self::$props = $_SESSION['KIT_REGIONS'];
        }

        list($realHost,)=explode(':',$_SERVER['HTTP_HOST']);
        $realHost = $protocol.$realHost;
        if(!self::$singleDomain && isset($_SESSION['KIT_REGIONS']['CODE']) && $realHost != $_SESSION['KIT_REGIONS']['CODE'])
        {
            unset($_SESSION['KIT_REGIONS']);
        }


        if(!is_array($_SESSION['KIT_REGIONS']))
        {
            unset($_SESSION['KIT_REGIONS']);
            self::$props = null;
        }

        if (is_null(self::$props))
        {
            $context = Application::getInstance()->getContext();
            $request = $context->getRequest();
            if (!$request->isAdminSection() && mb_strpos($request->getRequestedPage(),'regions.choose') === false)
            {
                $region = self::getCurrentLocation();
                self::setUserFields($region);
                $_SESSION['KIT_REGIONS'] = self::$props = $region;
                \Kit\Regions\Sale\Price::changeBasket();
            }
        }
    }

    protected static function setUserFields(&$region)
    {
        if($region['ID'])
        {
            // user fields
            $arUserType = \KitRegions::getUserTypeFields();

            // regions fields
            $arFields = FieldsTable::getList(
                [
                    'filter' => ['ID_REGION' => $region['ID']],
                    'cache'  => [
                        'ttl' => 36000000,
                    ],
                ]
            )->fetchAll();

            // Checking and adding missing fields
            if(count($arUserType) != count($arFields)) {
                $diff1 = array_keys($arUserType);
                $diff2 = array_map(function ($v){return $v['CODE'];}, $arFields);
                $diff = array_diff($diff1, $diff2);
                if(!empty($diff)) {
                    foreach ($diff as $value) {
                        $arFields[] = [
                            'CODE' => $value,
                            'VALUE' => ''
                        ];
                    }
                }
            }

            if(!empty($arUserType)) {
                foreach ($arFields as $field) {
                    // file type
                    if ($arUserType[$field['CODE']]['USER_TYPE_ID'] == "file") {
                        if (!empty($field['VALUE'])) {
                            // multiple
                            if($arUserType[$field['CODE']]['MULTIPLE'] == "Y") {
                                $field['VALUE'] = unserialize($field['VALUE']);
                                if (is_array($field['VALUE']) && !empty($field['VALUE'])) {
                                    foreach ($field['VALUE'] as $id) {
                                        $file = \CFile::GetFileArray($id);
                                        if (!empty($file)) {
                                            $region[$field['CODE']][] = $file;
                                        }
                                    }
                                }
                            } else {
                            // not multiple.
                                if($field['VALUE']) {
                                    $file = \CFile::GetFileArray($field['VALUE']);
                                    if (!empty($file)) {
                                        $region[$field['CODE']] = $file;
                                    }
                                }
                            }
                        }
                    } else if ($arUserType[$field['CODE']]['USER_TYPE_ID'] == "enumeration") {
                        if(!is_array($field['VALUE'])) {
                            $field['VALUE'] = [$field['VALUE']];
                        }

                        $res = \CUserFieldEnum::GetList(
                            [],
                            [
                                'USER_FIELD_ID' => $arUserType[$field['CODE']]['ID'],
                                'ID' => $field['VALUE']
                            ]);
                        while($item = $res->fetch()){
                            $region[$field['CODE']] = $item['VALUE'];
                        }
                    } else {
                    // other type
                        $unserialized = unserialize($field['VALUE']);
                        if (is_array($unserialized)) {
                            $field['VALUE'] = $unserialized;
                        }
                        $region[$field['CODE']] = $field['VALUE'];
                    }
                }
            }
        }
    }

    public static function getAutoRegion()
    {
        $region = self::getCurrentLocation();
        return $region;
    }

    /**
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public static function getCurrentLocation()
    {
        $Region = new Region();
        return $Region->findRegion();
    }


    /**
     * @return Variables
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param $code
     * @param $value
     */
    public function setProp($code, $value)
    {
        if (is_array(self::$props)) {
            self::$props = [];
        }
        self::$props[$code] = $value;
    }
}

?>