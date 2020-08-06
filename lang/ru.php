<?
$langValues = [
    'APPLICATION_TITLE' => 'Календарь',
];

// lib/helpers/ajax.php
$langValues['ERROR_BAD_ACTION'] = 'Запрос не обработан';
$langValues['ERROR_DATE_VALUE'] = 'Неправильный формат даты. Нужен формат ' . Day::CALENDAR_FORMAT;
$langValues['ERROR_EMPTY_TECHNIC_ID'] = 'В параметре technic не указан ID';
$langValues['ERROR_BAD_TECHNIC_ID'] = 'Передан неверный ID техники';
$langValues['ERROR_BAD_PARTNER_ID'] = 'Передан неверный ID партнера';
$langValues['ERROR_EMPTY_COMMENT_VALUE'] = 'Не указан комментарий';
$langValues['ERROR_EMPTY_COMMENT_BY_ID'] = 'Передан неверный идентификатор комментария';
$langValues['ERROR_COMMENT_AUTHOR_EDITING'] = 'Редактировать комментарий может только автор комментария';
$langValues['ERROR_EMPTY_TECHNIC_AND_COMMENT_IDS'] = 'Не указаны ни идентификатор техники, ни идентификатор партнера, ни идентификатор комментария';
$langValues['ERROR_EMPTY_PARTNER_TECHNIC_LIST'] = 'У указанного партнера нет ни одной техники';

// lib/helpers/bx24.restapi.class.php
$langValues['ERROR_EMPTY_PARAMS'] = 'Не указаны параметры domain, а так же либо access_token для обычного REST-запоса, '
                                  . 'либо webhook_token и webhook_userid для рабты через вебхук';
$langValues['ERROR_BAD_RESTAPI_METHOD_NAME'] = 'Для методов REST API надо использовать конструкцию call<СamelСase названия метода>(<параметры метода>)';

// lib/helpers/bpactivity.class.php
$langValues['ERROR_ACTIVITY_CODE'] = 'Этот запрос не может быть обработан этим действием БП';
$langValues['ERROR_EMPTY_ACTIVITY_PROPERTY'] = 'Не был указан параметр #PROPERTY#';
$langValues['ERROR_NO_ACTIVITY_INDEX_FILE'] = 'Для действия #ACTIVITY# отсутствует index.php';

// lib/helpers/bp.activities/content.add/index.php
$langValues['ERROR_PARENT_TECHNIC_OF_CONTENT'] = 'В БД нет информации о техники с внешним ID равным #ID#';

// lib/models/responsible.php
$langValues['ERROR_EMPTY_USER_ID'] = 'В параметре user не указан ID';

// lib/viewers/calendar.php
$langValues['FILTER_MY_TECHNIC'] = 'Своя техника';
$langValues['FILTER_TODAY_BUTTON'] = 'Сегодня';
$langValues['MANY_DEAL_STATUS'] = 'Техника работает на нескольких объектах';
$langValues['CONFIRM_MESSAGE_DELETING'] = 'Удалить комментарий?';

/**
 * Настройки выбора даты
 *
 * Дни, короткие названия
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

/**
 * Описание действий для БП в lib/bp.activities/*
 *
 * Основной заголовок списка с неутановленными действиями
 */
$langValues['BP_ACTIVITIES_INSTALLED_TITLE'] = 'Были установлены следующие действия для Бизнес-процессов';
$langValues['BP_ACTIVITIES_EMPTY_TITLE'] = 'Необходимо установить следующие действия для Бизнес-процессов';
$langValues['ACTIVITY_LIST_REMOVE_BUTTON'] = 'Удалить';
$langValues['ACTIVITY_LIST_INSTALL_BUTTON'] = 'Установить';
$langValues['ACTIVITY_LIST_CANCEL_BUTTON'] = 'Показать календарь';

// Действие БП для добавления контента
$langValues['BPA_CONTENT_ADD_TITLE'] = 'Календарь. Добавление контента';
$langValues['BPA_CONTENT_ADD_DESCRIPTION'] = 'Добавляет или обновляет данные контента';
$langValues['BPA_CONTENT_ADD_PARAM_TECHNIC_ID'] = 'ID техники';
$langValues['BPA_CONTENT_ADD_PARAM_SPECIFICATION_ID'] = 'ID элемента спецификации';
$langValues['BPA_CONTENT_ADD_PARAM_BEGIN_DATE'] = 'Дата начала';
$langValues['BPA_CONTENT_ADD_PARAM_FINISH_DATE'] = 'Дата окончания';
$langValues['BPA_CONTENT_ADD_PARAM_DEAL_URL'] = 'Ссылка на сделку';
$langValues['BPA_CONTENT_ADD_PARAM_RESPONSIBLE_NAME'] = 'Ответственный за сделку';
$langValues['BPA_CONTENT_ADD_PARAM_CUSTOMER_NAME'] = 'Заказчик';
$langValues['BPA_CONTENT_ADD_PARAM_WORK_ADDRESS'] = 'Адрес проведения работ';
$langValues['BPA_CONTENT_ADD_PARAM_STATUS'] = 'Статус сделки';
$langValues['BPA_CONTENT_ADD_PARAM_IS_CLOSED'] = 'Закрыты документы';

// Действие БП для добавления техники
$langValues['BPA_TECHNIC_ADD_TITLE'] = 'Календарь. Добавление техники';
$langValues['BPA_TECHNIC_ADD_DESCRIPTION'] = 'Добавляет или обновляет данные техники';
$langValues['BPA_CONTENT_ADD_PARAM_TECHNIC_ID'] = 'ID Техники';
$langValues['BPA_CONTENT_ADD_PARAM_NAME'] = 'Наименование';
$langValues['BPA_CONTENT_ADD_PARAM_STATE_NUMBER'] = 'Гос. Номер';
$langValues['BPA_CONTENT_ADD_PARAM_LOADING_CAPACITY'] = 'Грузоподъёмность';
$langValues['BPA_CONTENT_ADD_PARAM_PARTNER_ID'] = 'ID партнёра';
$langValues['BPA_CONTENT_ADD_PARAM_PARTNER_NAME'] = 'Наименование партнёра';
$langValues['BPA_CONTENT_ADD_PARAM_IS_MY'] = 'Своя/чужая';
$langValues['BPA_CONTENT_ADD_PARAM_VISIBILITY'] = 'Видимость в календаре';

// Данные модального окна с данными сделки и контента
$langValues['MODAL_CONTENT_TECHNIC_CAPTION'] = 'Техника';
$langValues['MODAL_CONTENT_RESPONSIBLE_CAPTION'] = 'Ответственный';