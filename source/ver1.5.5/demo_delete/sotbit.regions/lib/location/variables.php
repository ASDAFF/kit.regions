<?php

namespace Sotbit\Regions\Location;

use Sotbit\Regions\Config\Option;

class Variables
{
    /**
     * @var null
     */
    private static $instance = null;
    /**
     * @var array
     */
    private $codes = [];

    private function __clone() { }

    /**
     * Variables constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return null|Variables
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();

            if ($_SESSION['SOTBIT_REGIONS']
                && is_array($_SESSION['SOTBIT_REGIONS'])
            ) {
                self::$instance->setCodes(array_keys($_SESSION['SOTBIT_REGIONS']));
            }
        }

        return self::$instance;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function replaceContent($content = '')
    {
        $codes = $this->findVariables($content);
        $values = $this->getValues($codes);

        return str_replace(array_keys($values), array_values($values),
            $content);
    }

    /**
     * @param array       $codes
     * @param bool|string $lid
     *
     * @return array
     */
    public function getValues($codes = [], $lid = SITE_ID)
    {
        global $APPLICATION;
        $return = [];
        $delimiter = Option::get('MULTIPLE_DELIMITER', $lid);
        if ($codes)
        {
            foreach ($codes as $code)
            {
                if (!isset($_SESSION['SOTBIT_REGIONS'][$code]))
                {
                    continue;
                }

                $value = $_SESSION['SOTBIT_REGIONS'][$code];

                if($code == 'NAME' && isset($_SESSION['SOTBIT_REGIONS']['NAME']) &&
                    !empty($_SESSION['SOTBIT_REGIONS']['NAME']))
                    $value = $_SESSION['SOTBIT_REGIONS']['NAME'];

                switch ($code)
                {
                    case 'MAP_GOOGLE':
                        $ll = explode(',', $value['VALUE'][0]['VALUE']);
                        $data = [
                            'google_lat'   => $ll[0],
                            'google_lon'   => $ll[1],
                            'google_scale' => 10,
                            'PLACEMARKS'   => [
                                0 => [
                                    'LON'  => $ll[1],
                                    'LAT'  => $ll[0],
                                    'TEXT' => '',
                                ],
                            ],
                        ];
                        ob_start();
                        $APPLICATION->IncludeComponent(
                            "bitrix:map.google.view",
                            "",
                            [
                                "API_KEY"       => $value['API_KEY'],
                                "CONTROLS"      => [
                                    "SMALL_ZOOM_CONTROL",
                                    "TYPECONTROL",
                                    "SCALELINE",
                                ],
                                "INIT_MAP_TYPE" => "ROADMAP",
                                "MAP_DATA"      => serialize($data),
                                "MAP_HEIGHT"    => "500",
                                "MAP_ID"        => "1",
                                "MAP_WIDTH"     => "100%",
                                "OPTIONS"       => [
                                    "ENABLE_SCROLL_ZOOM",
                                    "ENABLE_DBLCLICK_ZOOM",
                                    "ENABLE_DRAGGING",
                                    "ENABLE_KEYBOARD",
                                ],
                            ]
                        );
                        $value = ob_get_contents();
                        ob_end_clean();
                        break;
                    case 'MAP_YANDEX':
                        $ll = explode(',', $value[0]['VALUE']);
                        $data = [
                            'yandex_lat'   => $ll[0],
                            'yandex_lon'   => $ll[1],
                            'yandex_scale' => 10,
                            'PLACEMARKS'   => [
                                0 => [
                                    'LON'  => $ll[1],
                                    'LAT'  => $ll[0],
                                    'TEXT' => '',
                                ],
                            ],
                        ];
                        ob_start();
                        $APPLICATION->IncludeComponent(
                            "bitrix:map.yandex.view",
                            "",
                            [
                                "CONTROLS"      => [
                                    "ZOOM",
                                    "MINIMAP",
                                    "TYPECONTROL",
                                    "SCALELINE",
                                ],
                                "INIT_MAP_TYPE" => "MAP",
                                "MAP_DATA"      => serialize($data),
                                "MAP_HEIGHT"    => "500",
                                "MAP_ID"        => "2",
                                'API_KEY'       => $value['API_KEY'],
                                "MAP_WIDTH"     => "100%",
                                "OPTIONS"       => [
                                    "ENABLE_SCROLL_ZOOM",
                                    "ENABLE_DBLCLICK_ZOOM",
                                    "ENABLE_DRAGGING",
                                ],
                            ]
                        );
                        $value = ob_get_contents();
                        ob_end_clean();
                        break;
                    default:
                        if (is_array($value)) {
                            $value = implode($value, $delimiter);
                        }
                        break;
                }
                $return[\SotbitRegions::genCodeVariable($code)] = $value;
            }
        }

        return $return;
    }

    /**
     * @param string $text
     *
     * @return array
     */
    private function findVariables($text = '')
    {
        $return = [];
        foreach ($this->getCodes() as $code) {
            $tag = \SotbitRegions::genCodeVariable($code);
            if(!empty($tag)) {
                if (mb_strpos($text, $tag) !== false) {
                    $return[] = $code;
                }
            }
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * @param array $codes
     */
    public function setCodes($codes)
    {
        $this->codes = $codes;
    }
}