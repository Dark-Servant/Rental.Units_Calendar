<?
session_start();

if (!defined('SESSION_CONTANTS') || !SESSION_CONTANTS || empty($_SESSION['CONST_LIST'])) {
    $_SESSION['CONST_LIST'] = array_keys(get_defined_constants());

    define('APPPATH', preg_replace('/[\/\\\\][^\/\\\\]*$/', '', $_SERVER['SCRIPT_NAME']));
    define('MAIN_SERVER_URL', $_SERVER['HTTP_REFERER'] ?? '');
    define('VERSION', '1.0.0');
    define('LANG', 'ru');
    define('ENV_CODE', 'dev');
    define('SHOW_VIEW', 'calendar');
    define('DAY_FORMAT', 'Y-m-d');

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