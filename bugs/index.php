<?
define('NOT_CHANGE_DUBLICATES', true);
define('NOT_CHANGE_COMMENT_HOST', true);

echo '*************** Contents' . PHP_EOL;
require_once __DIR__ . '/contents/dublicates/index.php';

echo '*************** Comments' . PHP_EOL;
require_once __DIR__ . '/comments/badhosts/index.php';