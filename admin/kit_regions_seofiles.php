<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

require_once($_SERVER['DOCUMENT_ROOT']
    .'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT']
    .'/bitrix/modules/main/include/prolog_admin_after.php');

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('kit.regions')) {
    return false;
}

// Request
$request = Application::getInstance()->getContext()->getRequest();

if ($request['run_sitemap'] == 'Y') {
    $sitemap = new Kit\Regions\Seo\Sitemap($request['site_id']);
    $rs = $sitemap->run();
    if ($rs->isSuccess()) {
        LocalRedirect(BX_ROOT."/admin/kit_regions_seofiles.php?lang="
            .LANGUAGE_ID.'&sitemap_ok=Y');
    } else {
        $errors = $rs->getErrors();
        foreach($errors as $error){
            $code = $error->getCode();
            break;
        }
        LocalRedirect(BX_ROOT."/admin/kit_regions_seofiles.php?lang="
            .LANGUAGE_ID.'&sitemap_error='.$code);
    }
}

if ($request['run_robots'] == 'Y') {
    $robots = new Kit\Regions\Seo\Robots($request['site_id']);
    $rs = $robots->run();
    if ($rs->isSuccess()) {
        LocalRedirect(BX_ROOT."/admin/kit_regions_seofiles.php?lang="
            .LANGUAGE_ID.'&robots_ok=Y');
    }
    else{
        $errors = $rs->getErrors();
        foreach($errors as $error){
            $code = $error->getCode();
            break;
        }
        LocalRedirect(BX_ROOT."/admin/kit_regions_seofiles.php?lang="
            .LANGUAGE_ID.'&robots_error='.$code);
    }
}

if ($request['sitemap_ok'] == 'Y') {
    CAdminMessage::ShowNote(Loc::getMessage(KitRegions::moduleId
        .'_SITEMAP_SUCCESS'));
} elseif ($request['sitemap_error'] > 0) {
    CAdminMessage::ShowMessage([
        "MESSAGE" => Loc::getMessage(KitRegions::moduleId.'_SITEMAP_ERROR_'
            .$request['sitemap_error']),
        "TYPE"    => "",
    ]);
}
if ($request['robots_ok'] == 'Y') {
    CAdminMessage::ShowNote(Loc::getMessage(KitRegions::moduleId
        .'_ROBOTS_SUCCESS'));
}
elseif ($request['robots_error'] > 0) {
    CAdminMessage::ShowMessage([
        "MESSAGE" => Loc::getMessage(KitRegions::moduleId.'_ROBOTS_ERROR_'
            .$request['robots_error']),
        "TYPE"    => "",
    ]);
}

// Sitemap
echo '<h3>'.Loc::getMessage(KitRegions::moduleId.'_SITEMAP_TITLE').'</h3>';

//submenu
$sites = KitRegions::getSites();
$actions = [];
foreach ($sites as $lid => $name) {
    $link = 'kit_regions_seofiles.php?lang='.LANGUAGE_ID.'&run_sitemap=Y&site_id='.$lid;
    $actions[] = [
        'TEXT' => '['.$lid.'] '.$name,
        'ONCLICK' => "RegionsWarning('".$link."');",
    ];

    $linkRobots = 'kit_regions_seofiles.php?lang='.LANGUAGE_ID.'&run_robots=Y&site_id='.$lid;
    $actionsRobots[] = [
        'TEXT' => '['.$lid.'] '.$name,
        'ONCLICK' => "RegionsWarning('".$linkRobots."');",
    ];
}
$aContext = [];
$aContext[] = [
    'TEXT'  => Loc::getMessage(KitRegions::moduleId.'_SITEMAP_GEN'),
    'TITLE' => Loc::getMessage(KitRegions::moduleId.'_SITEMAP_GEN'),
    'ICON'  => 'btn_new',
    'MENU'  => $actions,
];
$lAdminSitemap = new CAdminList('', []);
$lAdminSitemap->AddAdminContextMenu($aContext, false, false);
$lAdminSitemap->DisplayList();


// Robots
echo '<h3>'.Loc::getMessage(KitRegions::moduleId.'_ROBOTS_TITLE').'</h3>';
$robotsLink = 'kit_regions_seofiles.php?lang='.LANGUAGE_ID.'&run_robots=Y';
$aContext = [];
$aContext[] = [
    'TEXT'  => Loc::getMessage(KitRegions::moduleId.'_ROBOTS_GEN'),
    'TITLE' => Loc::getMessage(KitRegions::moduleId.'_ROBOTS_GEN'),
    'ICON'  => 'btn_new',
    'ONCLICK' => "RegionsWarning('".$robotsLink."');",
    'MENU'  => $actionsRobots,
];
$lAdminRobots = new CAdminList('', []);
$lAdminRobots->AddAdminContextMenu($aContext, false, false);
$lAdminRobots->DisplayList();


// Popup window
?>
    <script type="text/javascript">
        function RegionsWarning(link) {
            new BX.PopupWindow("regions_warning", null, {
                content: "<?=Loc::getMessage(KitRegions::moduleId
                    .'_POPUP_WARNING_BODY')?>",
                closeIcon: {right: "10px", top: "10px"},
                titleBar: "<?=Loc::getMessage(KitRegions::moduleId
                    .'_POPUP_WARNING_TITLE')?>",
                draggable: {restrict: false},
                buttons: [
                    new BX.PopupWindowButton({
                        text: "<?=Loc::getMessage(KitRegions::moduleId
                            .'_POPUP_OK')?>",
                        className: "popup-window-button-accept",
                        events: {click: function(e){
                                console.log(this.popupWindow);
                                BX.PreventDefault(e);
                                BX.adminPanel.Redirect([], link, e);
                            }}
                    }),
                    new BX.PopupWindowButton({
                        text: "<?=Loc::getMessage(KitRegions::moduleId
                            .'_POPUP_CLOSE')?>",
                        className: "webform-button-link-cancel",
                        events: {click: function(e){
                                this.popupWindow.close();
                                BX.PreventDefault(e);
                            }}
                    })
                ]
            }).show();
        }
    </script>
<?

require($_SERVER['DOCUMENT_ROOT']
    .'/bitrix/modules/main/include/epilog_admin.php');
?>