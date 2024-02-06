<?
namespace Bugs\Models\Solutions\Dublicates\Log;

use Log\Base as Logger;
use Bugs\Models\Solutions\Dublicates\Data\Base as BaseDublicate;

class Base
{
    protected $classUnits = [];
    protected $countOfGroups = 0;
    protected $otherOriginalIDs = [];

    public function __construct() {}

    public function addUnit(BaseDublicate $unit): self
    {
        $unit->setAsOriginalNextIDsOfDublicates($this->getOtherOriginalIDsForDublicates($unit));
        $unitOutData = new Unit($unit);
        $this->classUnits[] = $unitOutData;
        $this->countOfGroups += $unitOutData->getGroupCodeCount();
        return $this;
    }

    public function setOtherOriginalIDs(array $IDs): self
    {
        $this->otherOriginalIDs = $IDs;
        foreach ($this->classUnits as $classUnit) {
            $unit = $classUnit->getUnit();
            $unit->setAsOriginalNextIDsOfDublicates($this->getOtherOriginalIDsForDublicates($unit));
        }
        return $this;
    }

    public function replaceDublicates(): self
    {
        foreach ($this->classUnits as $classUnit) {
            $classUnit->getUnit()->replaceDublicates();
        }
        return $this;
    }

    public function getOtherOriginalIDsForDublicates(BaseDublicate $unit): array
    {
        return $this->otherOriginalIDs[get_class($unit)] ?? [];
    }

    public function getCountOfGroups(): int
    {
        return $this->countOfGroups;
    }

    public function sendTolog(): self
    {
        Logger::getMainInstance()->addNextValues(
            __METHOD__,
            '******* ALL DATA COUNT: ' . $this->countOfGroups, ''
        );

        foreach ($this->classUnits as $classUnit) {
            Logger::getMainInstance()->addNextValues(
                __METHOD__ . ' => ' . $classUnit->getUnit()->getModelName(),
                $classUnit->getData(),
                '*******************************', ''
            );
        }
        return $this;
    }
}