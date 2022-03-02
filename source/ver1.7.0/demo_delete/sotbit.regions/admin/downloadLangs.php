<?php
use Bitrix\Main\Localization\Loc;
?>
    <input type="file" id="download_file_whith_trinslite">
    <span
        id="downloadSpriner"
        style="display: none;"
    ></span>

<script>

window.addEventListener('load', function() {
    const input = document.getElementById('download_file_whith_trinslite');
    const spiner = document.getElementById('downloadSpriner');
    const form = document.querySelector('form');
    const spinerShowStyle = "background: url(/bitrix/panel/main/images/filter-active-waiter.gif) 10px 9px no-repeat scroll; padding: 5px 20px 20px 20px;";
    const spinerHideStyle = 'display: none;';
    input.addEventListener('change', function(e) {

        const files = e.target.files;

        if (files.length === 0) {
            return;
        }

        spiner.style = spinerShowStyle;
        for (let i = 0; i < files.length; i++) {
            const types = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

            if (!types.includes(files[i].type)) {
                alert('<?=Loc::getMessage('sotbit.regions_ADMIN_ERROR_TYPE_FILES')?>');
                input.parentNode.querySelector('span').innerText = '<?=Loc::getMessage('sotbit.regions_ADMIN_ADD_FILE')?>';
                spiner.style = spinerHideStyle;
                return;
            }

            const data = new FormData();
            data.append('inputText', files[i], 'country.csv')

            BX.ajax.runAction('sotbit:regions.AdminController.downloadLocationNames', {
                data: data,
            })
            .then(function(res) {
                input.parentNode.querySelector('span').innerText = '<?=Loc::getMessage('sotbit.regions_ADMIN_ADD_FILE')?>';
                spiner.style = spinerHideStyle;
                alert(JSON.stringify(res.data, null, 2));
            })
            .catch(function(res) {
                input.parentNode.querySelector('span').innerText = '<?=Loc::getMessage('sotbit.regions_ADMIN_ADD_FILE')?>';
                spiner.style = spinerHideStyle;
            })
        }
    })
})


</script>
