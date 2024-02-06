<?
namespace Bugs\Models\Solutions\Dublicates\Data\Contents;

use Bugs\Models\Solutions\Dublicates\Data\Base as BaseDublicates;
use SQL\FileByLastFolderIniting;

class Base extends BaseDublicates
{
    use FileByLastFolderIniting;

    protected $partOfOriginal = [];

    public function getDataViaQuery(): \PDOStatement
    {
        return $this->sqlCode->queryGetAllDublicates();
    }

    public function checkRequiringOfWorkWithNextRecord(array $record): bool
    {
        if (
            !isset($this->partOfOriginal[$record['specification_id']])
            && (
                !isset($this->originalCodeIDs[$record['specification_id']])
                || ($this->originalCodeIDs[$record['specification_id']] == $record['id'])
            )
        ) $this->partOfOriginal[$record['specification_id']] = $record['id'];

        return (
            !isset($this->partOfOriginal[$record['specification_id']])
            || ($this->partOfOriginal[$record['specification_id']] == $record['id'])
            || ($this->partOfOriginal[$record['specification_id']] != $record['sort'])
        );
    }

    public function getModelName(): string
    {
        return \Content::class;
    }

    public function getGroupCodeByRecord(array $record): string
    {
        return $record['specification_id'];
    }

    public function checkAsOriginalAtRecordForGroupData(array $record, array $groupData = null): bool
    {
        return !isset($this->originalCodeIDs[$record['specification_id']]) && empty($groupData['ID']);
    }
}