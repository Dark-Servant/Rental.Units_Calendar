<?
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/vendor/autoload.php';

define('ENV_CODE', 'dev');

ActiveRecord\Config::initialize(
    function($cfg) {
        $dbSettings = Yaml::parseFile('.php-database-migration/environments/' . ENV_CODE . '.yml');
        $cfg->set_model_directory('./models');
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
        ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';
    }
);


return [];

