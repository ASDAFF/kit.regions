<?
$moduleId = 'kit.regions';
$MESS[$moduleId.'_MAIN_SAVE']='Сохранить';
$MESS[$moduleId.'_TAB_1']='Настройки';
$MESS[$moduleId.'_TAB_2']='Переменные';
$MESS[$moduleId.'_TAB_3']='Добавить языки';
$MESS[$moduleId.'_WIDGET_LINK_DOWNLOAD'] = 'Выгрузить страны';
$MESS[$moduleId.'_WIDGET_CHARSET_UPLOD_FILE'] = 'Кодировка выгружаемых файлов';
$MESS[$moduleId.'_WIDGET_UPLOAD_CSV_FILE'] = 'Выгрузить .csv файл с названиями городов на имеющихся в системе языках';
$MESS[$moduleId.'_GROUP_MAIN_SETTINGS']='Основные настройки';
$MESS[$moduleId.'_GROUP_MAPS_SETTINGS']='Настройка карт';
$MESS[$moduleId.'_GROUP_VARIABLES']='Доступные переменные';
$MESS[$moduleId.'_GROUP_VARIABLES_SETTINGS']='Настройки';
$MESS[$moduleId.'_GROUP_DOWNLOAD_FILES_FOR_ADD_LANGS'] = 'Страны для выгрузки';
$MESS[$moduleId.'_WIDGET_IBLOCK_TYPE']='Тип инфоблока';
$MESS[$moduleId.'_WIDGET_IBLOCK_ID']='Код инфоблока';
$MESS[$moduleId.'_WIDGET_DOWNLOAD_NEW_LANGS'] = 'Загрузите csv файл с переводом';
$MESS[$moduleId.'_WIDGET_HL_ID']='Код highload';
$MESS[$moduleId.'_WIDGET_INSERT_SALE_LOCATION']='Подставлять местоположение на странице оформления заказа';
$MESS[$moduleId.'_WIDGET_SINGLE_DOMAIN']='Работать на одном домене';
$MESS[$moduleId.'_WIDGET_MODE_LOCATION']='Включить работу с местоположениями';
$MESS[$moduleId.'_WIDGET_AVAILABLE_VARIABLES']='Доступные переменные';
$MESS[$moduleId.'_WIDGET_AVAILABLE_VARIABLES_NAME']='Название';
$MESS[$moduleId.'_WIDGET_AVAILABLE_VARIABLES_CODE']='Код';
$MESS[$moduleId.'_WIDGET_AVAILABLE_VARIABLES_VARIABLE']='Переменная';
$MESS[$moduleId.'_WIDGET_MULTIPLE_DELIMITER']='Разделитель для множественных значений';
$MESS[$moduleId.'_WIDGET_PRICE_CODE']='Свойство типа цены';
$MESS[$moduleId.'_WIDGET_STORES']='Свойство склада';
$MESS[$moduleId.'_WIDGET_FIND_USER_METHOD']='Способ определения местоположения пользователя';
$MESS['kit.regions_DOWNLOAD_CONTRY_BTN'] = 'Выгрузить файлы';
$MESS[$moduleId.'_WIDGET_FIND_USER_METHOD_NOTE'] = '
Настройки геолокации с помощью модуля статистики находятся <a href="/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'mid_menu=1&mid=statistic">Настройки -> Настройки продукта -> Настройки модулей -> Веб-аналитика</a> на вкладке "Страна и город"<br>
Геолокация с помощью сервисов работает "из коробки", страница с настройками находится <a href="/bitrix/admin/geoip_handlers_list.php?lang='.LANGUAGE_ID.'">Настройки -> Настройки продукта -> Геолокация</a> <br>
База IP адресов SypexGeo Local обновляется агентом Kit\Regions\SypexGeo\SypexGeoUpdater::updater(); Лог обновлений базы IP адресов SypexGeo Local <a href="######">update.log</a><br>';

$MESS[$moduleId.'_WIDGET_ADD_ORDER_PROPERTY']='Добавлять регион в заказ';
$MESS[$moduleId.'_ADD_ORDER_PROPERTY_NOTE']='При включенной опции при оформлении заказа будет создаваться свойство с регионом пользователя';
$MESS[$moduleId.'_STATISTIC']='Модуль статистики';
$MESS[$moduleId.'_SERVICES'] = 'Сервисы геолокация';
$MESS[$moduleId.'_DEMO']='Модуль работает в демо-режиме. Приобрести полнофункциональную версию вы можете по адресу: <a href="http://marketplace.1c-bitrix.ru/solutions/'.$moduleId.'/" target="_blank">http://marketplace.1c-bitrix.ru/solutions/'.$moduleId.'/</a>';
$MESS[$moduleId.'_DEMO_END']='Демо-режим закончен. Приобрести полнофункциональную версию вы можете по адресу: <a href="http://marketplace.1c-bitrix.ru/solutions/'.$moduleId.'/" target="_blank">http://marketplace.1c-bitrix.ru/solutions/'.$moduleId.'/</a>';
$MESS[$moduleId.'_WIDGET_LOCATION_TYPE']='Тип местоположения "Город"';
$MESS[$moduleId.'_WIDGET_LOCATION_TYPE_NOTE']='<a href="/bitrix/admin/sale_location_type_list.php?lang=ru" target="__blank">Типы местоположений</a>';
$MESS[$moduleId.'_WIDGET_MAPS_MARKER']='Маркер местоположений';
$MESS[$moduleId.'_WIDGET_MAPS_MARKER_NOTE']='Используется при отсутствии изображения маркера в регионе.<br>При пустом значении используется стандартные маркеры сервиса карт.';

$MESS[$moduleId.'_WIDGET_MAPS_YANDEX_API']='Яндекс.Карты API ключ';
$MESS[$moduleId.'_WIDGET_MAPS_GOOGLE_API']='Google Карты API ключ';
$MESS[$moduleId.'_WIDGET_MAPS_GOOGLE_API_NOTE']='Используется при отсутствии ключа в регионе<br>
<a href="https://developer.tech.yandex.ru/keys/" target="__blank">Получить API ключ Яндекс.Карт</a><br>
<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="__blank">Получить API ключ Google Карт</a>';
?>