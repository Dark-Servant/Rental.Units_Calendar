<?
namespace Bugs\Models\Solutions\Dublicates\Data;

use SQL\Models\Relative\Belonging;
use SQL\{
    TableSet,
    Query
};
use Log\Base as Logger;

abstract class Base
{
    protected $data = [];
    protected $originalCodeIDs = [];

    abstract public function getDataViaQuery(): \PDOStatement;
    abstract public function checkAsOriginalAtRecordForGroupData(array $record, array $groupData = null): bool;

    abstract public function getGroupCodeByRecord(array $record): string;
    abstract public function getModelName(): string;

    public function loadData(): self
    {
        $this->data = [];
        $result = $this->getDataViaQuery();
        while ($record = $result->fetch()) {
            if (!$this->checkRequiringOfWorkWithNextRecord($record)) continue;

            $code = static::getGroupCodeByRecord($record);
            if (
                $this->checkAsSpecialOriginalForCodeNextRecord($code, $record)
                || $this->checkAsOriginalAtRecordForGroupData($record, $this->data[$code])
            ) {
                $this->data[$code]['ID'] = $record['id'];

            } else {
                $this->data[$code]['FROM'][] = $record['id'];
            }
        }

        Logger::getMainInstance()->addNextValues(
            '******* ' . __METHOD__,
            $this->data
        );

        return $this;
    }

    public function checkRequiringOfWorkWithNextRecord(array $unit): bool
    {
        return true;
    }

    protected function checkAsSpecialOriginalForCodeNextRecord(string $code, array $record): bool
    {
        return isset($this->originalCodeIDs[$code]) && ($this->originalCodeIDs[$code] == $record['id']);
    }

    public function getGroupCodes(): array
    {
        return array_keys($this->data);
    }

    public function getIDOfOriginalDataByCodeValue(string $code): int
    {
        return $this->data[$code]['ID'];
    }

    public function getIDsOfNotOriginalDataByCodeValue(string $code): array
    {
        return $this->data[$code]['FROM'] ?? [];
    }

    public function setAsOriginalNextIDsOfDublicates(array $codeIDs): self
    {
        if (!empty($this->data)) {
            foreach ($codeIDs as $code => $ID) {
                $this->setAsOriginalNextIDOfDublicateForCode($ID, $code);
            }
        }
        $this->originalCodeIDs = $codeIDs;
        return $this;
    }

    public function setAsOriginalNextIDOfDublicateForCode(int $ID, string $code): self // ?? TEST
    {
        if (
            !isset($this->data[$code]['ID'])
            || ($this->data[$code]['ID'] == $ID)
        ) return $this;

        if (is_array($this->data[$code]['FROM'])) {
            $fromIDOffset = array_search($ID, $this->data[$code]['FROM']);
            if ($fromIDOffset !== false) array_splice($this->data[$code]['FROM'], $fromIDOffset, 1);
        }

        $this->data[$code]['FROM'][] = $this->data[$code]['ID'];
        $this->data[$code]['ID'] = $ID;
        return $this;
    }

    public function replaceDublicates(): self
    {
        if (empty($this->data)) return $this;

        $badIDs = [];
        foreach ($this->data as $dublicate) {
            if (empty($dublicate['FROM'])) continue;

            array_push($badIDs, ...$dublicate['FROM']);
            foreach (static::getBelonging()->getGeneratorOfTableSetByValue($dublicate['FROM']) as $tableSet) {
                $tableSet->updateFieldsByValues([$dublicate['ID']]);
            }
        }

        (new TableSet(static::getBelonging()->getTableName()))->setAsConditionNextFieldValues(['id' => $badIDs])->deleteData();
        return $this;
    }

    public static function getBelonging(): Belonging
    {
        static $belonging = [];

        $modelClass = static::getModelName();
        if (isset($belonging[$modelClass])) return $belonging[$modelClass];

        return $belonging[$modelClass] = (new Belonging($modelClass))->initData();
    }
}