<?
namespace Bugs\Models\Solutions\Dublicates\Data\Responsibles;

use Bugs\Models\Solutions\Dublicates\Data\Base as BaseDublicates;
use SQL\FileByLastFolderIniting;

class Base extends BaseDublicates
{
    use FileByLastFolderIniting;

    public function getDataViaQuery(): \PDOStatement
    {
        return $this->sqlCode->queryGetResponsibleDublicates();
    }

    public static function getModelName(): string
    {
        return \Responsible::class;
    }

    public static function getGroupCodeByRecord(array $record): string
    {
        return md5(mb_strtolower(preg_replace('/[^\wа-я]+/ui', ' ', $record['name'])));
    }

    public function checkAsOriginalAtRecordForGroupData(array $record, array $groupData = null): bool
    {
        return !empty($record['external_id']) && empty($groupData['ID']);
    }
}