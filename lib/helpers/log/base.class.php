<?
namespace Log;

class Base
{
    protected static $mainInstance = false;

    protected $path = false;
    protected $classNameRights = [];
    protected $checkClassRights = true;

    public function __construct(string $path)
    {
        $this->initByFolderPathValue($path);
    }

    public function initByFolderPathValue(string $path): self
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            if (!file_exists($path)) return $this;

        } elseif (!is_dir($path)) {
            return $this;
        }
        $this->path = rtrim($path, '\\/') . '/' . date('YmdHis') . '.txt';

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->isInited() ? $this->path : null;
    }

    public function deleteFilePath(): self
    {
        if (!$this->isInited()) return $this;

        @unlink($this->path);
        $this->path = false;
        return $this;
    }

    public function addRightsForClassNames(): self
    {
        if (!$this->isInited()) return $this;

        foreach (func_get_args() as $className) {
            if (!is_string($className)) continue;

            $this->addRightsForClassName($className);
        }
        return $this;
    }

    public function addRightsForClassName(string $className): self
    {
        if (!$this->canAddRightsForClassName($className)) return $this;

        $this->classNameRights[] = strtolower($className);
        return $this;
    }

    public function removeRightsForClassNames(): self
    {
        foreach (func_get_args() as $className) {
            if (!is_string($className)) continue;

            $this->removeRightsForClassName($className);
        }
        return $this;
    }

    public function removeRightsForClassName(string $className, bool $orIsParent = false): self
    {
        if (!class_exists($className)) return $this;

        $lowerClassName = strtolower($className);
        $nearParentClassName = $this->getClassNameWithNearRightsByName($className);
        if (!$nearParentClassName || (($lowerClassName != $nearParentClassName) && !$orIsParent))
            return $this;

        array_splice($this->classNameRights, array_search($nearParentClassName, $this->classNameRights), 1);
        return $this;
    }

    public function addNextValues(): self
    {
        $className = static::getLastClassCallerName(array_slice(debug_backtrace(), 1));
        if (!$this->getClassNameWithNearRightsByName($className))
            return $this;

        $this->checkClassRights = false;
        foreach (func_get_args() as $argValue) {
            $this->addNextValueForClassName($argValue, $className);
        }
        $this->checkClassRights = true;
        return $this;
    }

    protected function getLastClassCallerName(array $debugBacktrace): ?string
    {
        foreach ($debugBacktrace as $callerData) {
            if (isset($callerData['class'])) return $callerData['class'];
        }
    }

    public function addNextValueForClassName($value, string $className): self
    {
        if ($this->checkClassRights && !$this->getClassNameWithNearRightsByName($className)) return $this;

        file_put_contents($this->path, static::getOutDataForValue($value) . PHP_EOL, FILE_APPEND);
        return $this;
    }

    public function canAddRightsForClassName(string $className): bool
    {
        return $this->isInited() && class_exists($className) && !$this->getClassNameWithNearRightsByName($className);
    }

    public function isInited(): bool
    {
        return $this->path !== false;
    }

    public function getClassNameWithNearRightsByName(string $name): ?string
    {
        if (!class_exists($name)) return null;

        $lowerClassName = strtolower($name);
        $parentsClassNames = array_map(
                                    function($name) { return strtolower($name); },
                                    class_parents($name)
                                );
        foreach ($this->classNameRights as $unitClassName) {
            if (
                ($lowerClassName == $unitClassName)
                || in_array($unitClassName, $parentsClassNames)
            ) return $unitClassName;
        }
        return null;
    }

    protected static function getOutDataForValue($value)
    {
        return is_object($value) || is_array($value) ? print_r($value, true) : $value;
    }

    public static function getMainInstance(): self
    {
        if (self::$mainInstance !== false) return self::$mainInstance;

        return self::$mainInstance = new static($_SERVER['DOCUMENT_ROOT'] . '/log');
    }

    public static function setMainInstance(self $instance): self
    {
        return self::$mainInstance = $instance;
    }
}