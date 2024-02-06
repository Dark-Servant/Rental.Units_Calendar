<?
namespace SQL;

class File
{
    protected $fileName;
    protected $sqlList = [];

    public function __construct(string $file)
    {
        $this->initFile($file);
    }

    public function initFile(string $file): self
    {
        $this->fileName = false;
        if (!file_exists($file) || is_dir($file)) return $this;

        $this->fileName = $file;
        foreach (preg_split('/(?:^|[\r\n])-- */', trim(file_get_contents($this->fileName))) as $sqlUnit) {
            if (!preg_match('/^([^:]*):[^\r\n]+[\r\n]\s*([\W\w]*)/', $sqlUnit, $sqlUnitParts)) continue;

            $sqlCode = preg_replace('/(?:^\s*|[\s*;]+$)/', '', $sqlUnitParts[2]);
            if (empty($sqlCode)) continue;

            $this->sqlList[strtoupper(preg_replace('/\W+/', '_', $sqlUnitParts[1]))] = $sqlCode;
        }

        return $this;
    }

    public function isInited(): bool
    {
        return $this->fileName !== false;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function __call(string $methodName, array $params)
    {
        if (preg_match('/^query(\w+)$/', $methodName, $methodNameParts))
            return $this->getQueryByCodeWithParams($methodNameParts[1], ...$params);
    }

    public function getQueryByCodeWithParams(string $code, array $params = []): ?\PDOStatement
    {
        $code = ltrim(
                    preg_replace_callback(
                        '/([A-Z]+[a-z]*)/',
                        function($parts) {
                            return '_' . strtoupper($parts[0]);
                        },
                        $code
                    ),
                    '_'
                );
        if (empty($this->sqlList[$code])) return null;

        return static::getQueryByTemplateWithParams($this->sqlList[$code], $params);
    }

    public static function getQueryByTemplateWithParams(string $template, array $params): ?\PDOStatement
    {
        $preparedParams = array_map(
                                function($value) {
                                    if (!is_array($value)) {
                                        return $value;

                                    } elseif (empty($value)) {
                                        return '""';

                                    } else {
                                        return join(', ', $value);
                                    }
                                },
                                $params
                            );

        return (new Query(strtr($template, $preparedParams)))->send();
    }
}
