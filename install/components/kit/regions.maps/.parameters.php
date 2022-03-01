<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Kit\Regions\Internals\FieldsTable;
use Kit\Regions\Maps;
use Kit\Regions\Internals\RegionsTable;

if (!\Bitrix\Main\Loader::includeModule('kit.regions')) {
    return;
}

// Regions
$regions = [];
$rs = RegionsTable::getList([]);
while ($region = $rs->fetch()) {
    $regions[$region['ID']] = '['.$region['ID'].'] '.$region['NAME'];
}
$regionsIds = array_keys($regions);

// marker fields
$mapMarkerFields = Maps\Base::getUserFields();
$mapMarkerFieldsDefault = Maps\Base::DEFAULT_USER_FIELDS;

// Type map
$mapsType = Maps\Base::getMapsType();
$mapsTypeKeys = array_keys($mapsType);
$mapsTypeDefault = reset($mapsTypeKeys);
$mapTypeCurrent = $mapsTypeDefault;

if (!empty($templateProperties['TYPE']) && in_array($templateProperties['TYPE'], $mapsTypeKeys)) {
    $mapTypeCurrent = $templateProperties['TYPE'];
}
if (!empty($arCurrentValues['TYPE']) && in_array($arCurrentValues['TYPE'], $mapsTypeKeys)) {
    $mapTypeCurrent = $arCurrentValues['TYPE'];
}

// Visual
$mapsVisual = [
    'yandex' => [
        'all'     => [
            'MAP'           => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_MAP'),
            'SATELLITE'     => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_SATELLITE'),
            'HYBRID'        => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_HYBRID'),
            'PUBLIC'        => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_PUBLIC'),
            'PUBLIC_HYBRID' => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_PUBLIC_HYBRID'),
        ],
        'default' => 'NORMAL',
    ],
    'google' => [
        'all'     => [
            'ROADMAP'   => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_MAP'),
            'SATELLITE' => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_SATELLITE'),
            'HYBRID'    => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_HYBRID'),
            'TERRAIN'   => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE_TERRAIN'),
        ],
        'default' => 'MAP',
    ],
];
$mapInitType = $mapsVisual[$mapTypeCurrent]['all'];
$mapInitTypeDefault = $mapsVisual[$mapTypeCurrent]['default'];

// Controls
$mapControls = [
    'yandex' => [
        'all'     => [
            /*'TOOLBAR' => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_TOOLBAR'),*/
            'ZOOM'        => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_ZOOM'),
            'SMALLZOOM'   => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_SMALLZOOM'),
            'MINIMAP'     => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_MINIMAP'),
            'TYPECONTROL' => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_TYPECONTROL'),
            'SCALELINE'   => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_SCALELINE'),
            'SEARCH'      => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_SEARCH'),
        ],
        'default' => [/*'TOOLBAR', */
            'ZOOM',
            'MINIMAP',
            'TYPECONTROL',
            'SCALELINE',
        ],
    ],
    'google' => [
        'all'     => [
            'SMALL_ZOOM_CONTROL' => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_SMALL_ZOOM_CONTROL'),
            'TYPECONTROL'        => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_TYPECONTROL'),
            'SCALELINE'          => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS_SCALELINE'),
        ],
        'default' => ['SMALL_ZOOM_CONTROL', 'TYPECONTROL', 'SCALELINE'],
    ],
];
$mapInitControls = $mapControls[$mapTypeCurrent]['all'];
$mapInitControlsDefault = $mapControls[$mapTypeCurrent]['default'];

// Options
$mapOptionsArray = [
    'yandex' => [
        'all'     => [
            'ENABLE_SCROLL_ZOOM'     => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS_ENABLE_SCROLL_ZOOM'),
            'ENABLE_DBLCLICK_ZOOM'   => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS_ENABLE_DBLCLICK_ZOOM'),
            'ENABLE_RIGHT_MAGNIFIER' => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS_ENABLE_RIGHT_MAGNIFIER'),
            'ENABLE_DRAGGING'        => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS_ENABLE_DRAGGING'),
        ],
        'default' => ['ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING'],
    ],
    'google' => [
        'all'     => [
            'ENABLE_SCROLL_ZOOM'   => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS_ENABLE_SCROLL_ZOOM'),
            'ENABLE_DBLCLICK_ZOOM' => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS_ENABLE_DBLCLICK_ZOOM'),
            'ENABLE_DRAGGING'      => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS_ENABLE_DRAGGING'),
            'ENABLE_KEYBOARD'      => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS_ENABLE_KEYBOARD'),
        ],
        'default' => ['ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING', 'ENABLE_KEYBOARD'],
    ],
];
$mapOptions = $mapOptionsArray[$mapTypeCurrent]['all'];
$mapOptionsDefault = $mapOptionsArray[$mapTypeCurrent]['default'];

// Scale
$mapScale = Maps\Base::getScale();
$mapScaleDefault = Maps\Base::DEFAULT_SCALE;


$arComponentParameters = [
    "GROUPS"     => [],
    "PARAMETERS" => [
        "TYPE"    => [
            "NAME"              => GetMessage(\KitRegions::entityId."_MAPS_TYPE"),
            "TYPE"              => "LIST",
            "MULTIPLE"          => "N",
            "VALUES"            => $mapsType,
            "PARENT"            => "BASE",
            "ADDITIONAL_VALUES" => 'N',
            "REFRESH"           => "Y",
            "DEFAULT"           => $mapsTypeDefault,
        ],
        "REGIONS" => [
            "PARENT"   => "BASE",
            "NAME"     => GetMessage(\KitRegions::entityId."_MAPS_REGIONS"),
            "TYPE"     => "LIST",
            "VALUES"   => $regions,
            "MULTIPLE" => "Y",
            "SIZE"     => "5",
            "DEFAULT"  => $regionsIds,
        ],

        "MARKER_FIELDS" => [
            "PARENT"   => "BASE",
            "NAME"     => GetMessage(\KitRegions::entityId."_MAPS_MARKER_FIELDS"),
            "TYPE"     => "LIST",
            "VALUES"   => $mapMarkerFields,
            "MULTIPLE" => "Y",
            "SIZE"     => "5",
            "DEFAULT"  => $mapMarkerFieldsDefault,
        ],

        'INIT_MAP_TYPE' => [
            'NAME'              => GetMessage(\KitRegions::entityId.'_MAPS_INIT_MAP_TYPE'),
            'TYPE'              => 'LIST',
            'VALUES'            => $mapInitType,
            'DEFAULT'           => $mapInitTypeDefault,
            'ADDITIONAL_VALUES' => 'N',
            'PARENT'            => 'VISUAL',
        ],

        'MAP_WIDTH' => [
            'NAME'    => GetMessage(\KitRegions::entityId.'_MAPS_WIDTH'),
            'TYPE'    => 'STRING',
            'DEFAULT' => Maps\Base::DEFAULT_WIDTH,
            'PARENT'  => 'VISUAL',
        ],

        'MAP_HEIGHT' => [
            'NAME'    => GetMessage(\KitRegions::entityId.'_MAPS_HEIGHT'),
            'TYPE'    => 'STRING',
            'DEFAULT' => Maps\Base::DEFAULT_HEIGHT,
            'PARENT'  => 'VISUAL',
        ],

        'MAP_SCALE'            => [
            'NAME'    => GetMessage(\KitRegions::entityId.'_MAPS_SCALE'),
            'TYPE'    => 'LIST',
            'VALUES'  => $mapScale,
            'DEFAULT' => $mapScaleDefault,
            'PARENT'  => 'VISUAL',
        ],
        'MAP_CALCULATE_CENTER' => [
            'NAME'    => GetMessage(\KitRegions::entityId.'_MAP_CALCULATE_CENTER'),
            'TYPE'    => 'CHECKBOX',
            'DEFAULT' => 'Y',
            'PARENT'  => 'VISUAL',
        ],

        'CONTROLS' => [
            'NAME'     => GetMessage(\KitRegions::entityId.'_PARAM_CONTROLS'),
            'TYPE'     => 'LIST',
            'MULTIPLE' => 'Y',
            'VALUES'   => $mapInitControls,
            'DEFAULT'  => $mapInitControlsDefault,
            'PARENT'   => 'VISUAL',
        ],

        'OPTIONS' => [
            'NAME'     => GetMessage(\KitRegions::entityId.'_PARAM_OPTIONS'),
            'TYPE'     => 'LIST',
            'MULTIPLE' => 'Y',
            'VALUES'   => $mapOptions,
            'DEFAULT'  => $mapOptionsDefault,
            'PARENT'   => 'VISUAL',
        ],
    ],
];
?>