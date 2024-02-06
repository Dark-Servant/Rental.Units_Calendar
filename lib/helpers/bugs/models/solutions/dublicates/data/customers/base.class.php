<?
namespace Bugs\Models\Solutions\Dublicates\Data\Customers;

use Bugs\Models\Solutions\Dublicates\Data\Base as BaseDublicates;
use SQL\FileByLastFolderIniting;

class Base extends BaseDublicates
{
    use FileByLastFolderIniting;

    public function getDataViaQuery(): \PDOStatement
    {
        return $this->sqlCode->queryGetCustomerDublicates();
    }

    public static function getModelName(): string
    {
        return \Customer::class;
    }

    public static function getGroupCodeByRecord(array $record): string
    {
        return md5(mb_strtolower(preg_replace('/[^\wа-я]+/ui', ' ', $record['name'])));
    }

    public function checkAsOriginalAtRecordForGroupData(array $record, array $groupData = null): bool
    {
        return empty($groupData['ID']);
    }
}