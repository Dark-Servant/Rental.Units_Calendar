<?
namespace Bugs\Models\Solutions\Dublicates\Log;

use Bugs\Models\Solutions\Dublicates\Data\Base as BaseDublicate;

class Unit
{
    protected $unit;
    protected $groupCodeCount = 0;
    protected $loggerClassName = false;

    protected const LOG_CLASS_TEMPLATE = '\\Bugs\\Models\\Log\\%s';

    public function __construct(BaseDublicate $unit)
    {
        $this->unit = $unit;
        $this->groupCodeCount = count($this->unit->loadData()->getGroupCodes());
        $this->initLoggerClassName();
    }

    protected function initLoggerClassName(): self
    {
        $loggerClassName = sprintf(static::LOG_CLASS_TEMPLATE, basename($this->unit->getModelName()));
        if (!class_exists($loggerClassName)) return $this;

        $this->loggerClassName = $loggerClassName;
        return $this;
    }

    public function getUnit(): BaseDublicate
    {
        return $this->unit;
    }

    public function getGroupCodeCount(): int
    {
        return $this->groupCodeCount;
    }

    public function getData(): array
    {
        if (!$this->loggerClassName) return [];

        $categoryLog = [];
        $modelName = $this->unit->getModelName();
        $loggerClassName = $this->loggerClassName;
        foreach ($this->unit->getGroupCodes() as $code) {
            $originalData = (new $loggerClassName($modelName::find_by_id($this->unit->getIDOfOriginalDataByCodeValue($code))))->getData();
            $originalID = array_key_first($originalData);
            $categoryLog[$code] = [
                    'ORIGINAL' => ['ID' => $originalID] + $originalData[$originalID],
                    'COPIES' => []
                ];
            foreach ($this->unit->getIDsOfNotOriginalDataByCodeValue($code) as $ID) {
                $categoryLog[$code]['COPIES'] += (new $loggerClassName($modelName::find_by_id($ID)))->getData();
            }
        }

        return $categoryLog;
    }
}