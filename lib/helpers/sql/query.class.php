<?
namespace SQL;

use ActiveRecord\ConnectionManager;
use Log\Base as Logger;

class Query implements \IteratorAggregate
{
    protected static $sendingStatus = true;

    protected $query;

    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function send(): ?\PDOStatement
    {
        Logger::getMainInstance()->addNextValues(
            __METHOD__,
            'SQL (' . intval(static::$sendingStatus) . '): ' . $this->query . PHP_EOL
        );

        return static::$sendingStatus ? ConnectionManager::get_connection()->query($this->query) : null;
    }

    public function getIterator(): \Traversable
    {
        while ($data = $this->send()) {
            yield $data;
        }
    }

    public static function activateSending(bool $status = true)
    {
        static::$sendingStatus = $status;
    }
}