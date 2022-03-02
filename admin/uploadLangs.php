<?php
use Bitrix\Main\Localization\Loc;
?>

<button
    id="dowload_contry_files"
    class="adm-btn"
><?=Loc::getMessage('kit.regions_DOWNLOAD_CONTRY_BTN')?></button>
<div id="link_conteiner" style="padding: 5px;"></div>

<script>
    window.addEventListener('load', function() {
        const elementsId = <?=CUtil::PhpToJSObject($this->getSetting('elementsId'));?>;
        const btn = document.getElementById('dowload_contry_files');
        const charset = document.getElementById('CHARSET_UPLOD_FILE');
        const elements = Object.keys(elementsId).map(function (i) {
            const element = document.getElementById(elementsId[i]);
            element.setAttribute('data-id', i);
            return element;
        });
        const submit = document.querySelector('input[type=submit]');
        submit.addEventListener('click', function(e) {
            charset.setAttribute('disabled', true);
            elements.forEach(function(i) {
                i.setAttribute('disabled', true);
            })
        })
        const linkConteiner = document.getElementById('link_conteiner');

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            Array.prototype.slice.call(linkConteiner.childNodes).forEach(function(i) {
                i.remove();
            });
            const check = elements.filter(function(i) {
                return i.checked ? true : false;
            });

            check.forEach(i => {
                BX.ajax.runAction('kit:regions.AdminController.uploadLocationNames', {
                    data: {
                        contryId: i.getAttribute('data-id'),
                        contry: i.id,
                        charset: charset.value,
                    },
                })
                .then(function(res) {
                    const link = document.createElement('a')
                    // link.setAttribute('donwload', true);
                    link.setAttribute('href', res.data);
                    link.setAttribute('style', 'margin-right: 5px;');
                    link.innerText = i.id;
                    linkConteiner.appendChild(link);
                })
                .catch(function(res)  {
                    alert('error: ' + JSON.stringify(res, null, 2))
                })
            });

        });

    })
</script>