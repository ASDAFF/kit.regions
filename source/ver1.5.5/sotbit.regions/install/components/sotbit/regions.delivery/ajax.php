<?
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
    define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if (!\Bitrix\Main\Loader::includeModule('sotbit.regions'))
    return;

$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
    $template = $request->get('template');
    $parameters = (string)$request->get('parameters');
    $id = $request->get('Id');
}
catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
{
    echo $e->getMessage();
    die();
}

$params = unserialize(base64_decode($parameters));

if(isset($params["AJAX"]))
    unset($params["AJAX"]);

if(isset($params["START_AJAX"]))
    unset($params["START_AJAX"]);

if($id > 0)
{
    $params['LOCATION_TO'] = $id;
}

$APPLICATION->IncludeComponent(
    'sotbit:regions.delivery',
    $template,
    $params,
    false
);