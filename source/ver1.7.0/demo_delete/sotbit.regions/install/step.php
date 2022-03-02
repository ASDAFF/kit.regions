<?
if (!check_bitrix_sessid())
    return;

IncludeModuleLangFile(__FILE__);
$moduleId = 'sotbit.regions';
?>
<script>
    function button(form) {
        form.submit();
    }
</script>
<form action="<?echo $APPLICATION->GetCurPage(); ?>">
    <?echo bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?echo LANG ?>">
    <input type="hidden" name="step" value="1">
    <input type="hidden" name="id" value="<?=$moduleId?>">
    <input type="hidden" name="install" value="Y">
    <input type="button" name="" onclick="button(this.form)" value="<?echo GetMessage("APPLY"); ?>">
    <form>