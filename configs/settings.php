<?
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/consts.php';
require_once __DIR__ . '/../lang/' . LANG . '.php';

ActiveRecord\Config::initialize(
    function($cfg) {
        $dbSettings = Yaml::parseFile(__DIR__ . '/.php-database-migration/environments/' . ENV_CODE . '.yml');
        $cfg->set_model_directory(__DIR__ . '/../lib/models');
        $connection = $dbSettings['connection']['driver'] . '://'
                    . $dbSettings['connection']['username'] . ':'
                    . $dbSettings['connection']['password'] . '@'
                    . 'localhost:' . $dbSettings['connection']['port'] . '/'
                    . $dbSettings['connection']['database'];

        $cfg->set_connections([
            'development' => $connection,
            'test' => $connection,
            'production' => $connection,
        ]);
        ActiveRecord\Connection::$datetime_format = DAY_FORMAT . ' H:i:s';
    }
);

return [];