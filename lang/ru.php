<?
$langValues = [
    'APPLICATION_TITLE' => 'Календарь',
];

// lib/helpers/ajax.php
$langValues['ERROR_BAD_ACTION'] = 'Запрос не обработан';

// lib/helpers/bx24.restapi.class.php
$langValues['ERROR_EMPTY_PARAMS'] = 'Не указаны параметры domain, а так же либо access_token для обычного REST-запоса, '
                                  . 'либо webhook_token и webhook_userid для рабты через вебхук';
$langValues['ERROR_BAD_RESTAPI_METHOD_NAME'] = 'Для методов REST API надо использовать конструкцию call<СamelСase названия метода>(<параметры метода>)';