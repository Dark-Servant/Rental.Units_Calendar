<?
use Symfony\Component\Yaml\Yaml;

error_reporting(E_ERROR);

define('PHP_ACTIVERECORD_AUTOLOAD_DISABLE', true);

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

spl_autoload_register('infoservice_auotload', false, true);

function infoservice_auotload($className)
{
    $classPlaces = [
        $_SERVER['DOCUMENT_ROOT'] . '/lib/models/#classname#.php'
    ];

    $specialNameSpaces = $_SERVER['DOCUMENT_ROOT'] . '/lib/helpers';
    if (($namespaces = ActiveRecord\get_namespaces($className))) {
        $className = array_pop($namespaces);
        $specialNameSpaces .= '/' . strtolower(implode('/', $namespaces));
    }
    $specialClassNameTemplates = [
        $specialNameSpaces . '/#classname#.class.php',
        $specialNameSpaces . '/#classname#.trait.php',
    ];
    if (empty($namespaces)) {
        array_push($classPlaces, ...$specialClassNameTemplates);
        
    } else {
        $classPlaces = $specialClassNameTemplates;
    }
    $className = strtolower($className);
    foreach ($classPlaces as $unitPath)  {
        $file = str_replace('#classname#', $className, $unitPath);
        if (!file_exists($file)) continue;

        require_once $file;
        return;
    }
}

require_once __DIR__ . '/consts.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lang/' . LANG . '.php';

ActiveRecord\Config::initialize(
    function($cfg) {
        $dbSettings = Yaml::parseFile(__DIR__ . '/.php-database-migration/environments/' . ENV_CODE . '.yml');
        $connection = $dbSettings['connection']['driver'] . '://'
                    . $dbSettings['connection']['username'] . ':'
                    . $dbSettings['connection']['password'] . '@'
                    . 'localhost:' . $dbSettings['connection']['port'] . '/'
                    . $dbSettings['connection']['database']
                    . '?charset=' . $dbSettings['connection']['charset'];

        $cfg->set_connections([
            'development' => $connection,
            'test' => $connection,
            'production' => $connection,
        ]);
        ActiveRecord\Connection::$datetime_format = Day::FORMAT . ' H:i:s';
    }
);

return [];
