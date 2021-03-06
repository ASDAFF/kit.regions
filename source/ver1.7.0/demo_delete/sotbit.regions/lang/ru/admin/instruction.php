<?php
$MESS['sotbit.regions_instruction'] = '
<tr><td colspan="2">
    <div align="left" class="adm-info-message-wrap"><div class="adm-info-message">
        <h4>Пример действий необходимых для вывода названий городов на украинском языке для сайта s1:</h4>
        <ol>
            <li>Создать язык интерфейса Переходим в Настройки-> <p> Настройки продукта->Региональные параметры->Языки интерфейса. Создаем украинский язык с символьным кодом ua.</li>
            <li>Для сайта s1 указать глобальную константу LANGUAGE_ID <p> Переходим в Настройки-> Настройки продукта->Сайты->Список сайтов->Изменить настройки сайта s1 ->Язык) и указываем символьный код ua</li>
            <li>Выгрузить файл с наименованием городов <p> Переходим в Сотбит->Мультирегиональность->Настройки-> [s1] Магазин-> Добавить языки. <p> Указываем страну Україна, указываем кодировку файла выгрузки (пример windows-1251), нажимаем кнопку "Выгрузить файлы" </li>
            <li>Редактируем выгруженный файл <p> В первый свободный столбец необходимо добавить код языка (ua), а во второй наименование города на украинском языке. В файл можно добавлять сразу несколько языков.</li>
            <table border="1" cellpadding="4" align="center">
                <tr>
                    <td>4329</td> <td>9464</td> <td>en</td> <td>Selidovo</td> <td>ru</td> <td>Селидове</td> <td></td> <td></td>
                </tr>
                <tr>
                    <td>4349</td> <td>1115884</td> <td>en</td> <td>Avdeevka</td> <td>ru</td> <td>Авдеевка</td> <td>ua</td> <td>Авдіївка</td>
                </tr>
            </table>
        </ol>
        <h4>Уточнения по формату файла:</h4>
        <ol>
            <li>Разделение столбцов знаком точка с запятой (;), формат загружаемого файла csv</li>
            <li>Строка 1: Первоначальный вид выгрузки (такие записи можно не удалять)</li>
            <li>Строка 2: Правильный формат строки для загрузки перевода</li>
            <div style="color: red;">Неправильный формат добавления записи: если указан код языка, но не указано значение перевода, то такая запись будет игнорироваться при загрузке</div>
            <li>Загрузить файл с переводами в модуль</li>
        </ol>
        <div>Переходим в Сотбит->Мультирегиональность->Настройки-> [s1] Магазин-> Добавить языки, нажимаем кнопку «Добавить файл». Все переводы наименований городов  хранятся в таблице b_sale_loc_name.</div>
</div></div>
</td></tr>
';