<?
use Symfony\Component\Yaml\Yaml;

error_reporting(E_ERROR);

define('PHP_ACTIVERECORD_AUTOLOAD_DISABLE', true);

require_once dirname(__DIR__) . '/vendor/autoload.php';

spl_autoload_register('infoservice_auotload', false, true);

function infoservice_auotload($className)
{
    $path = ActiveRecord\Config::instance()->get_model_directory();
    $root = realpath(isset($path) ? $path : '.');

    if (($namespaces = ActiveRecord\get_namespaces($className))) {
        $className = array_pop($namespaces);
        $root .= DIRECTORY_SEPARATOR . implode($namespaces, DIRECTORY_SEPARATOR);
    }

    $className = strtolower($className);    
    foreach ([
            $root . '/#classname#.php',
            dirname(__DIR__) . '/lib/models/#classname#.php',
            dirname(__DIR__) . '/lib/helpers/#classname#.class.php'
        ] as $unitPath)  {

        $file = str_replace('#classname#', $className, $unitPath);
        if (!file_exists($file)) continue;

        require_once $file;
        return;
    }
}

require_once __DIR__ . '/consts.php';
require_once dirname(__DIR__) . '/lang/' . LANG . '.php';

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
        ActiveRecord\Connection::$datetime_format = Day::DAY_FORMAT . ' H:i:s';
    }
);

return [];