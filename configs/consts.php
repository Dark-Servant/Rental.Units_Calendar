<?
define('SESSION_CONTANTS', false);

/**
 * Из-за использования прокси может случиться, что скрипты *.js.php
 * будут использовать другие сессии
 */
if (SESSION_CONTANTS && !empty($_REQUEST['sid'])) session_id($_REQUEST['sid']);
session_start();

if (!SESSION_CONTANTS || empty($_SESSION['CONST_LIST'])) {
    $_SESSION['CONST_LIST'] = array_keys(get_defined_constants());

    define('APPPATH', preg_replace('/[\/\\\\][^\/\\\\]*$/', '/', $_SERVER['SCRIPT_NAME']));
    define('MAIN_SERVER_URL', $_SERVER['HTTP_REFERER'] ?? '');

    if (!empty($_REQUEST['DOMAIN']) && isset($_REQUEST['AUTH_ID'])) {
        define('DOMAIN', $_REQUEST['DOMAIN']);
        define('AUTH_ID', $_REQUEST['AUTH_ID']);
    }

    define('VERSION', time());//'1.0.0');
    define('URL_SCRIPT_FINISH', 'sid=' . session_id() . '&' . VERSION);

    define('LANG', 'ru');
    define('ENV_CODE', 'dev');
    define('SHOW_VIEW', 'calendar');
    define('CONTENT_CLOSED_DEAL_STATUS', array_search('closed', Content::CONTENT_DEAL_STATUS));
    define('CONTENT_MAX_DEAL_STATUS', CONTENT_CLOSED_DEAL_STATUS - 1);
    define('CONTENT_MANY_DEAL_STATUS', CONTENT_CLOSED_DEAL_STATUS + 1);
    define('CONTENT_REPAIR_DEAL_STATUS', CONTENT_CLOSED_DEAL_STATUS + 2);
    define('TECHNIC_SORTING', ['IS_MY DESC, LOADING_CAPACITY ASC']);

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