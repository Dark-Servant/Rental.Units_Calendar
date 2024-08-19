<?
/**
 * Из-за использования прокси может случиться, что скрипты *.js.php
 * будут использовать другие сессии
 */
if (!empty($_SERVER['HTTP_INFOSERVICE_AJAX'])) session_id($_SERVER['HTTP_INFOSERVICE_AJAX']);
session_start();

if (empty($_SERVER['HTTP_INFOSERVICE_AJAX']) || empty($_SESSION['CONST_LIST'])) {
    $_SESSION['CONST_LIST'] = array_keys(get_defined_constants());

    define('APPPATH', preg_replace('/[\/\\\\][^\/\\\\]*$/', '/', $_SERVER['SCRIPT_NAME']));
    define('MAIN_SERVER_URL', (
                    $_SERVER['HTTP_ORIGIN']
                    ?? preg_replace('/(https?:\/\/[^\/]+)(?:\/[\w\W]*)?/i', '$1', $_SERVER['HTTP_REFERER'])
                    ?? ''
                ) . '/'
            );

    if (!empty($_REQUEST['DOMAIN']) && isset($_REQUEST['AUTH_ID'])) {
        define('DOMAIN', $_REQUEST['DOMAIN']);
        define('AUTH_ID', $_REQUEST['AUTH_ID']);
    }

    define('VERSION', '1.5.1');
    define('URL_SCRIPT_FINISH', 'sid=' . session_id() . '&' . VERSION);

    define('LANG', 'ru');
    define('ENV_CODE', 'dev');
    define('SHOW_VIEW', 'calendar');
    define('DAY_SECOND_COUNT', 86400);
    define('WEEK_DAY_COUNT', 7);
    define('CONTENT_CLOSED_DEAL_STATUS', array_search('closed', Content::CONTENT_DEAL_STATUS));
    define('CONTENT_MAX_DEAL_STATUS', CONTENT_CLOSED_DEAL_STATUS - 1);
    define('CONTENT_MANY_DEAL_STATUS', CONTENT_CLOSED_DEAL_STATUS + 1);
    define('CONTENT_REPAIR_DEAL_STATUS', CONTENT_CLOSED_DEAL_STATUS + 2);
    define('TECHNIC_SORTING', ['IS_MY DESC, LOADING_CAPACITY ASC']);

    /**
     * Константы, используемые для открытия окна создания CRM-сделки
     * 
     * Символьный код пользовательского поля для CRM-сделок "ID техники"
     */
    define('CRM_USER_FIELD_TECHNIC_ID', 'UF_CRM_1604312301');

    // Символьный код пользовательского поля для CRM-сделок "Дата начала работ"
    define('CRM_USER_FIELD_START_DATE', 'UF_CRM_1571574579');

    // Символьный код пользовательского поля для CRM-сделок "Дата окончания работ"
    define('CRM_USER_FIELD_COMPLETION_DATE', 'UF_CRM_1571574620');

    // *******************************************************************

    // Коды дежурных комментариев
    define('DUTY_COMMENT_REPAIR_STATUS', 1); // комментарий "Ремонт"
    define('DUTY_COMMENT_ON_ROAD_STATUS', 2); // комментарий "В дороге"
    define('DUTY_COMMENT_BASED_ON_STATUS', 3); // комментарий "На базе "
    define('DUTY_COMMENT_WEEKEND_STATUS', 4); // комментарий "Выходной"
    
    $_SESSION['CONST_LIST'] = array_filter(
                    get_defined_constants(),
                    function($key) {
                        return !in_array($key, $_SESSION['CONST_LIST']);
                    }, ARRAY_FILTER_USE_KEY
                );

} else {
    foreach ($_SESSION['CONST_LIST'] as $constName => $constValue) {
        define($constName, $constValue);
    }
}