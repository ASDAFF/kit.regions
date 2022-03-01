<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
// array user fields
$arUserType = \KitRegions::getUserTypeFields();

$frame = $this->createFrame()->begin("");
if($arResult['FIELDS']):
    ?>
    <ul>
        <?foreach($arResult['FIELDS'] as $key => $field):?>
            <li>
                <?=$field['NAME']?>:
                <?if($arUserType[$field['CODE']]['USER_TYPE_ID'] == "file"):
                    if($arUserType[$field['CODE']]['MULTIPLE'] == "Y"):
                        foreach ($field['VALUE'] as $img):
                            ?><a href="<?=$img['SRC']?>"><?=$img['ORIGINAL_NAME']?></a>
                        <?endforeach;
                    else:
                        ?><a href="<?=$field['VALUE']['SRC']?>"><?=$field['VALUE']['ORIGINAL_NAME']?></a>
                    <?endif;
                else:?>
                    <?=(is_array($field['VALUE']))?implode(', ',$field['VALUE']):$field['VALUE']?>
                <?endif;?>
            </li>
        <?endforeach;?>
    </ul>
<?
endif;
?>