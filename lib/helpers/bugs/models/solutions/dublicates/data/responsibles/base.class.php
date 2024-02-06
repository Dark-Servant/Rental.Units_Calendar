<?
namespace Bugs\Models\Solutions\Dublicates\Data\Responsibles;

use Bugs\Models\Solutions\Dublicates\Data\Base as BaseDublicates;
use SQL\FileByLastFolderIniting;

class Base extends BaseDublicates
{
    use FileByLastFolderIniting;

    protected $groupCodeResult = [];

    public function getDataViaQuery(): \PDOStatement
    {
        return $this->sqlCode->queryGetResponsibleDublicates();
    }

    public function getModelName(): string
    {
        return \Responsible::class;
    }

    public function getGroupCodeByRecord(array $record): string
    {
        $name = mb_strtolower(preg_replace('/[^\wа-я]+/ui', ' ', $record['name']));
        $code = md5($name);
        if (
            $this->groupCodeResult[$code]
            && (
                empty($record['external_id'])
                || ($this->groupCodeResult[$code] === true)
                || ($this->groupCodeResult[$code] === $record['external_id'])
            )
        ) {
            if (!empty($record['external_id']))
                $this->groupCodeResult[$code] = $record['external_id'];
            return $code;

        } elseif (empty($record['external_id'])) {
            $this->groupCodeResult[$code] = true;
            return $code;

        } else {
            return md5($name . $record['external_id']);
        }
    }

    public function checkAsOriginalAtRecordForGroupData(array $record, array $groupData = null): bool
    {
        return empty($groupData['ID']) && !empty($record['external_id']);
    }
}