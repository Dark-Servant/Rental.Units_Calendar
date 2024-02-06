<?
namespace Bugs\Models\Log;

use Models\InfoserviceBase as BaseModel;

abstract class Base
{
    protected $unit;

    public function __construct(BaseModel $unit)
    {
        $this->unit = $unit;
    }

    abstract public function getData(): array;
}