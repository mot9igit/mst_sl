<?php

$_lang['area_shoplogistic_main'] = 'Основные';
$_lang['area_shoplogistic_eshoplogistic'] = 'eShopLogistic';
$_lang['area_shoplogistic_city'] = 'Города и папки';
$_lang['area_shoplogistic_requizites'] = 'Реквизиты';
$_lang['area_shoplogistic_crm'] = 'CRM';
$_lang['area_shoplogistic_products'] = 'Товары';
$_lang['area_shoplogistic_order'] = 'Заказ';
$_lang['area_shoplogistic_cdek'] = 'СДЭК';
$_lang['area_shoplogistic_yandex'] = 'Yandex';
$_lang['area_shoplogistic_postrf'] = 'Почта России';
$_lang['area_shoplogistic_parserdata'] = 'Parserdata';
$_lang['area_shoplogistic_bonus'] = 'Бонусная система';

$_lang['setting_shoplogistic_frontend_css'] = 'СSS-файл для фронта';
$_lang['setting_shoplogistic_frontend_css_desc'] = 'Можно указать тут свой файл или перенести стили в свой css-файл и очистить поле.';
$_lang['setting_shoplogistic_frontend_js'] = 'JS-файл для фронта';
$_lang['setting_shoplogistic_frontend_js_desc'] = 'Можно указать тут свой скрипт или перенести логику в свой js-файл и очистить поле.';
$_lang['setting_shoplogistic_bonus_percent'] = '% начисления от стоимости товара за покупку в маркетплейсе';
$_lang['setting_shoplogistic_bonus_percent_store'] = '% начисления от стоимости товара за покупку в магазине';
$_lang['setting_shoplogistic_bonus_percent_desc'] = 'Работает глобально на все товары';
$_lang['setting_shoplogistic_max_bonus_percent_deduction'] = '% списания бонусов за покупку';
$_lang['setting_shoplogistic_max_bonus_percent_deduction_desc'] = 'Работает глобально на все товары';


$_lang['setting_shoplogistic_api_key'] = 'Ключ API eShopLogistic';
$_lang['setting_shoplogistic_api_key_desc'] = '<a href="https://eshoplogistic.ru" target="_blank">eshoplogistic.ru</a>';
$_lang['setting_shoplogistic_api_key_dadata'] = 'Ключ API DaData';
$_lang['setting_shoplogistic_api_key_dadata_desc'] = '<a href="https://dadata.ru/" target="_blank">dadata.ru</a>';
$_lang['setting_shoplogistic_secret_key_dadata'] = 'Secret key API DaData';
$_lang['setting_shoplogistic_secret_key_dadata_desc'] = '<a href="https://dadata.ru/" target="_blank">dadata.ru</a>';
$_lang['setting_shoplogistic_default_delivery'] = 'Способ доставки по-умолчанию';
$_lang['setting_shoplogistic_default_delivery_desc'] = 'ID способа доставки MS2, если не получено ни одного результата по другим вариантам.';

$_lang['setting_shoplogistic_curier_delivery'] = 'Способ доставки курьером';
$_lang['setting_shoplogistic_curier_delivery_desc'] = 'ID способа доставки MS2 курьером, у доставки нужен класс обработчик slHandler.';
$_lang['setting_shoplogistic_store_colors'] = 'Цвета магазинов';
$_lang['setting_shoplogistic_store_colors_desc'] = 'Для отображения в корзине и заказах. Указываем черех запятую. Например, #cccccc,$ffffff.';
$_lang['setting_shoplogistic_punkt_delivery'] = 'Способ доставки в пункт выдачи';
$_lang['setting_shoplogistic_punkt_delivery_desc'] = 'ID способа доставки MS2 в пункт выдачи, у доставки нужен класс обработчик slHandler.';
$_lang['setting_shoplogistic_pickup_delivery'] = 'Способ доставки Самовывоз';
$_lang['setting_shoplogistic_pickup_delivery_desc'] = 'ID способа доставки MS2 Самовывоз, у доставки нужен класс обработчик slHandler.';
$_lang['setting_shoplogistic_express_delivery'] = 'Способ доставки Экспресс';
$_lang['setting_shoplogistic_express_delivery_desc'] = 'ID способа доставки MS2 Экспресс, у доставки нужен класс обработчик slHandler.';

$_lang['setting_shoplogistic_blank_image'] = 'Изображение заглушка';
$_lang['setting_shoplogistic_blank_image_desc'] = 'Укажите путь до изображения-заглушки относительно корня сайта';

$_lang['setting_shoplogistic_post_delivery'] = 'Способ доставки почтой России';
$_lang['setting_shoplogistic_post_delivery_desc'] = 'ID способа доставки MS2 почтой России, у доставки нужен класс обработчик slHandler.';

$_lang['setting_shoplogistic_regexp_gen_code'] = 'Маска для генерации ключа API';
$_lang['setting_shoplogistic_regexp_gen_code_desc'] = 'sl-/([a-zA-Z0-9]{4-10})/';
$_lang['setting_shoplogistic_open_fields_store'] = 'Поля доступные для редактирование в ЛК Магазина';
$_lang['setting_shoplogistic_open_fields_store_desc'] = 'Список ключей через запятую';
$_lang['setting_shoplogistic_open_fields_warehouse'] = 'Поля доступные для редактирование в ЛК Склада';
$_lang['setting_shoplogistic_open_fields_warehouse_desc'] = 'Список ключей через запятую';
$_lang['setting_shoplogistic_tax_percent'] = 'Процент комиссии для вознаграждений';
$_lang['setting_shoplogistic_tax_percent_desc'] = 'Данный процент будет вычитаться при начислении финансов за заказ';
$_lang['setting_shoplogistic_default_store'] = 'Магазин по умолчанию (только на доставку)';
$_lang['setting_shoplogistic_default_store_desc'] = "У данного магазина будет игнорироваться остаток товара. При отображении товаров будет учитываться параметр \"Не доступен для заказа\"";

$_lang['setting_shoplogistic_cart_mode'] = 'Режим работы корзины';
$_lang['setting_shoplogistic_cart_mode_desc'] = '1 - ищем глобально ближайший магазин, 2 - ищем только в определенных магазинах';

$_lang['setting_shoplogistic_mode'] = 'Режим работы';
$_lang['setting_shoplogistic_mode_desc'] = '1 - показываем остатки магазина и дистрибьютора, 2 - показываем все остатки, заказ отсылаем в ближайший магазин';

$_lang['setting_shoplogistic_alert_mode'] = 'Режим работы уведомлений';
$_lang['setting_shoplogistic_alert_mode_desc'] = '1 - уведомляем стандартно, 0 - не уведомляем';

$_lang['setting_shoplogistic_tocrm'] = 'Отправлять данные в CRM?';
$_lang['setting_shoplogistic_tocrm_desc'] = '1 - отправлять, 0 - не отправлять';

$_lang['setting_shoplogistic_cart_to_warehouse'] = 'Отправлять ли заказ к ближайшему дистру?';
$_lang['setting_shoplogistic_cart_to_warehouse_desc'] = 'Если не найден магазин с остатком корзины';

$_lang['setting_shoplogistic_phx_prefix'] = 'Префикс плейсхолдеров';
$_lang['setting_shoplogistic_cityfolder_phx_prefix_desc'] = 'По данному префиксу можно получить доступ к плейсхолдерам';

$_lang['setting_shoplogistic_city_fields'] = 'Поля таблицы';
$_lang['setting_shoplogistic_city_fields_desc'] = 'Поля таблицы городов';

$_lang['setting_shoplogistic_catalogs'] = 'Каталоги, участвующие в данном городе';
$_lang['setting_shoplogistic_catalogs_desc'] = 'Лучше глобально использовать компонент';

$_lang['setting_shoplogistic_km'] = 'Кол-во километров для определения ближайшего города';
$_lang['setting_shoplogistic_km_desc'] = 'Если расстояние больше данного значения, то выберется город по умолчанию. Если данный параметр не нужен, напишите 0';

$_lang['setting_shoplogistic_ur_name'] = 'Юридическое лицо';
$_lang['setting_shoplogistic_ur_name_desc'] = 'Необходимо для документов';

$_lang['setting_shoplogistic_inn'] = 'ИНН';
$_lang['setting_shoplogistic_inn_desc'] = 'Необходимо для документов';

$_lang['setting_shoplogistic_kpp'] = 'КПП';
$_lang['setting_shoplogistic_kpp_desc'] = 'Необходимо для документов, если нет оставьте пустым';

$_lang['setting_shoplogistic_crm_webhook'] = 'Webhook';
$_lang['setting_shoplogistic_crm_webhook_desc'] = 'Входящий вебхук';

$_lang['setting_shoplogistic_crm_product_key_field'] = 'Ключевое поле товара';
$_lang['setting_shoplogistic_crm_product_key_field_desc'] = 'Необходимо для проверки на дублирование в CRM, перед установкой необходимо выставить соответствие в настройках';

$_lang['setting_shoplogistic_crm_link_products'] = 'Прилинковать товары?';
$_lang['setting_shoplogistic_crm_link_products_desc'] = 'Для начала товары нужно связать с CRM';

$_lang['setting_shoplogistic_check_percent'] = 'Процент цены товара для проверки на публикацию';
$_lang['setting_shoplogistic_check_percent_desc'] = 'Если цена товара будет отличаться на +- этот процент, то он будет снят с публикации и отвязан от товара';

$_lang['setting_shoplogistic_default_stage'] = 'ID стадии нового заказа';
$_lang['setting_shoplogistic_default_stage_desc'] = '';

$_lang['setting_shoplogistic_payment_stage'] = 'ID стадии оплаченного заказа';
$_lang['setting_shoplogistic_payment_stage_desc'] = '';
$_lang['setting_shoplogistic_assigned_by_id'] = 'ID ответственного за сделки';
$_lang['setting_shoplogistic_assigned_by_id_desc'] = '';
$_lang['setting_shoplogistic_type_id'] = 'Тип сделки';
$_lang['setting_shoplogistic_type_id_desc'] = '';

$_lang['setting_shoplogistic_debug_log'] = 'Вести лог расчетов?';
$_lang['setting_shoplogistic_debug_log_desc'] = 'Вся информация будет в файле core/cache/logs/sl_calc.log';

// СДЭК
$_lang['setting_shoplogistic_cdek_test_url'] = 'URL тестового API';
$_lang['setting_shoplogistic_cdek_test_url_desc'] = 'По умолчанию: https://api.edu.cdek.ru/v2/';
$_lang['setting_shoplogistic_cdek_test_account'] = 'Client ID для тестовых запросов';
$_lang['setting_shoplogistic_cdek_test_account_desc'] = 'По умолчанию: EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI';
$_lang['setting_shoplogistic_cdek_test_pass'] = 'Password для тестовых запросов';
$_lang['setting_shoplogistic_cdek_test_pass_desc'] = 'По умолчанию: PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG';
$_lang['setting_shoplogistic_cdek_url'] = 'URL для боевых запросов';
$_lang['setting_shoplogistic_cdek_url_desc'] = 'По умолчанию: https://api.cdek.ru/v2/';
$_lang['setting_shoplogistic_cdek_account'] = 'Client ID';
$_lang['setting_shoplogistic_cdek_account_desc'] = 'Смотреть в ЛК СДЭК';
$_lang['setting_shoplogistic_cdek_pass'] = 'Password';
$_lang['setting_shoplogistic_cdek_pass_desc'] = 'Смотреть в ЛК СДЭК';
$_lang['setting_shoplogistic_cdek_test_mode'] = 'Тестовый режим';
$_lang['setting_shoplogistic_cdek_test_mode_desc'] = '';
$_lang['setting_shoplogistic_cdek_token'] = 'Токен';
$_lang['setting_shoplogistic_cdek_token_desc'] = 'Заполняется автоматически';
$_lang['setting_shoplogistic_cdek_token_expired_in'] = 'Токен истекает';
$_lang['setting_shoplogistic_cdek_token_expired_in_desc'] = 'Заполняется автоматически';

// Yandex
$_lang['setting_shoplogistic_yandex_oauth_token'] = 'Токен';
$_lang['setting_shoplogistic_yandex_oauth_token_desc'] = 'См. в ЛК Я.Доставки';
$_lang['setting_shoplogistic_yandex_express_url'] = 'URL API для экспресс доставки';
$_lang['setting_shoplogistic_yandex_express_url_desc'] = 'См. в <a href="https://yandex.ru/dev/logistics/api/about/access.html">документации</a>';
$_lang['setting_shoplogistic_yandex_express_url_test'] = 'URL API TEST для экспресс доставки';
$_lang['setting_shoplogistic_yandex_express_url_test_desc'] = 'См. в <a href="https://yandex.ru/dev/logistics/api/about/access.html">документации</a>';
$_lang['setting_shoplogistic_yandex_delivery_url'] = 'URL API для доставки';
$_lang['setting_shoplogistic_yandex_delivery_url_desc'] = 'См. в <a href="https://yandex.ru/dev/logistics/delivery-api/doc/about/access.html">документации</a>';
$_lang['setting_shoplogistic_yandex_delivery_url_test'] = 'URL API TEST для доставки';
$_lang['setting_shoplogistic_yandex_delivery_url_test_desc'] = 'См. в <a href="https://yandex.ru/dev/logistics/delivery-api/doc/about/access.html">документации</a>';
$_lang['setting_shoplogistic_yandex_delivery_platform_id_test'] = 'ID тестовой платформы';
$_lang['setting_shoplogistic_yandex_delivery_platform_id_test_desc'] = 'См. в <a href="https://yandex.ru/dev/logistics/delivery-api/doc/about/access.html">документации</a>';

// Почта России
$_lang['setting_shoplogistic_postrf_token'] = 'Токен';
$_lang['setting_shoplogistic_postrf_token_desc'] = 'См. в ЛК <a href="https://otpravka.pochta.ru/">Почты России</a>';
$_lang['setting_shoplogistic_postrf_url'] = 'URL API';
$_lang['setting_shoplogistic_postrf_url_desc'] = 'См. в <a href="https://otpravka.pochta.ru/">ЛК</a>';
$_lang['setting_shoplogistic_postrf_key'] = 'Ключ в Base64';
$_lang['setting_shoplogistic_postrf_key_desc'] = 'Можно сгенерировать в <a href="https://otpravka.pochta.ru/specification#/authorization-key">документации</a>';

// Заказ
$_lang['setting_shoplogistic_code_live'] = 'Срок жизни кода выдачи';
$_lang['setting_shoplogistic_code_live_desc'] = 'В секундах, 0 - не обновлять';
$_lang['setting_shoplogistic_regenerate_code'] = 'Перегенерировать код?';
$_lang['setting_shoplogistic_regenerate_code_desc'] = '';

// Parserdata
$_lang['setting_shoplogistic_parserdata_token'] = 'Токен';
$_lang['setting_shoplogistic_parserdata_token_desc'] = 'См. в ЛК <a href="https://apimarket.parserdata.ru/">Parserdata</a>';
$_lang['setting_shoplogistic_parserdata_url'] = 'URL API';
$_lang['setting_shoplogistic_parserdata_url_desc'] = 'См. в ЛК <a href="https://apimarket.parserdata.ru/">Parserdata</a>';