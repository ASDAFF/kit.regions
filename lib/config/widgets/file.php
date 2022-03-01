<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 23-Jan-18
 * Time: 9:59 AM
 */

namespace Kit\Regions\Config\Widgets;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Localization\Loc;
use Kit\Regions\Config\Option;
use Kit\Regions\Config\Widget;

Loc::loadMessages(__FILE__);

class File extends Widget
{

    public function show()
    {

        $settings = $this->getSettings();
        if(empty($settings['size'])) {
            $settings['size'] = [
                'height' => 30
            ];
        }

        if(empty($settings['filetype'])) {
            $settings['filetype'] = 'jpg,jpeg,png,gif';
        }

        /*if(empty($settings['default'])) {
            $settings['default'] = '/bitrix/themes/.default/icons/kit.regions/icon.png';
        }*/

        $value = htmlspecialcharsbx(Option::get($this->getCode(),$settings['SITE_ID']));

        if(empty($value) && !empty($settings['default'])) {
            $value = htmlspecialcharsbx($settings['default']);
        }

        $code = $this->getCode();
        $name = "...";
        \CAdminFileDialog::ShowScript( Array (
            'event' => 'BX_FD_'.$code,
            'arResultDest' => Array (
                'FUNCTION_NAME' => 'BX_FD_ONRESULT_'.$code
            ),
            'arPath' => Array (),
            'select' => 'F',
            'operation' => 'O',
            'showUploadTab' => true,
            'showAddToMenuTab' => false,
            'fileFilter' => $settings['filetype'],
            'allowAllFiles' => true,
            'SaveConfig' => true
        ) );

        ?>
        <input id="__FD_PARAM_<?=$code?>" name="<?=$code?>" value="<?=$value?>" type="text" />
        <input value="<?=$name?>" type="button" onclick="window.BX_FD_<?=$code?>();" />
        <script>
            setTimeout(function(){
                if (BX("bx_fd_input_strtolower(' <?=$code?> ')"))
                    BX("bx_fd_input_strtolower(' <?=$code?> ')").onclick = window.BX_FD_<?=$code?>;
            }, 200);
            window.BX_FD_ONRESULT_<?=$code?> = function(filename, filepath)
            {
                var oInput = BX("__FD_PARAM_<?=$code?>");
                if (typeof filename == "object")
                    oInput.value = filename.src;
                else
                    oInput.value = (filepath + "/" + filename).replace(/\/\//ig, "\'/\'");
            }
        </script>
        <?
        if(!empty($value) && !empty($settings['preview'])) {
            ?><br><img src="<?=$value?>" alt="" height="<?=$settings['size']['height']?>>"/><?
        }
    }
}