<?
namespace SQL;

trait FileByLastFolderIniting
{
    protected $sqlCode;

    public function __construct(File $sqlCode = null)
    {
        $this->sqlCode = static::getRealSQLFile($sqlCode);
    }

    public static function getRealSQLFile(File $sqlCode = null): File
    {
        if (isset($sqlCode)) return $sqlCode;

        $classPath = dirname((new \ReflectionClass(static::class))->getFileName());
        $SQLFilePath = $classPath . '/' . basename($classPath) . '.sql';
        return new File($SQLFilePath);
    }
}