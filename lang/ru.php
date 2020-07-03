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

// lib/viewers/calendar.php
$langValues['FILTER_MY_TECHNIC'] = 'Своя техника';
$langValues['FILTER_TODAY_BUTTON'] = 'Сегодня';
$langValues['MANY_DEAL_STATUS'] = 'Техника работает на нескольких объектах';

/**
 * Настройки выбора даты
 *
 *  Дни, короткие названия
 */
$langValues['DATE_CHOOOSING']['DAYS']['SHORT']['SUN'] = 'Вск';
$langValues['DATE_CHOOOSING']['DAYS']['SHORT']['MON'] = 'Пнд';
$langValues['DATE_CHOOOSING']['DAYS']['SHORT']['TUE'] = 'Втр';
$langValues['DATE_CHOOOSING']['DAYS']['SHORT']['WED'] = 'Ср';
$langValues['DATE_CHOOOSING']['DAYS']['SHORT']['THU'] = 'Чтв';
$langValues['DATE_CHOOOSING']['DAYS']['SHORT']['FRI'] = 'Птн';
$langValues['DATE_CHOOOSING']['DAYS']['SHORT']['SAT'] = 'Сбб';
// Дни, полные названия
$langValues['DATE_CHOOOSING']['DAYS']['FULL']['SUNDAY'] = 'Воскресенье';
$langValues['DATE_CHOOOSING']['DAYS']['FULL']['MONDAY'] = 'Понедельник';
$langValues['DATE_CHOOOSING']['DAYS']['FULL']['TUESDAY'] = 'Вторник';
$langValues['DATE_CHOOOSING']['DAYS']['FULL']['WEDNESDAY'] = 'Среда';
$langValues['DATE_CHOOOSING']['DAYS']['FULL']['THURSDAY'] = 'Четверг';
$langValues['DATE_CHOOOSING']['DAYS']['FULL']['FRIDAY'] = 'Пятница';
$langValues['DATE_CHOOOSING']['DAYS']['FULL']['SATURDAY'] = 'Суббота';
// месяцы
$langValues['DATE_CHOOOSING']['MONTHS']['JANUARY'] = 'Январь';
$langValues['DATE_CHOOOSING']['MONTHS']['FEBRUARY'] = 'Февраль';
$langValues['DATE_CHOOOSING']['MONTHS']['MARCH'] = 'Март';
$langValues['DATE_CHOOOSING']['MONTHS']['APRIL'] = 'Апрель';
$langValues['DATE_CHOOOSING']['MONTHS']['MAY'] = 'Май';
$langValues['DATE_CHOOOSING']['MONTHS']['JUNE'] = 'Июнь';
$langValues['DATE_CHOOOSING']['MONTHS']['JULY'] = 'Июль';
$langValues['DATE_CHOOOSING']['MONTHS']['AUGUST'] = 'Август';
$langValues['DATE_CHOOOSING']['MONTHS']['SEPTEMBER'] = 'Сентябрь';
$langValues['DATE_CHOOOSING']['MONTHS']['OCTOBER'] = 'Октябрь';
$langValues['DATE_CHOOOSING']['MONTHS']['NOVEMBER'] = 'Ноябрь';
$langValues['DATE_CHOOOSING']['MONTHS']['DECEMBER'] = 'Декабрь';
// Подсказка для указания года
$langValues['DATE_CHOOOSING_YEAR'] = 'Укажите год';