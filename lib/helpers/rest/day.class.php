<?
namespace REST;

use \Day as MainDay;
use \Quarter;

/**
 * Undocumented class
 */
class Day
{
    protected $result = null;
    protected $startTimeStamp;

    /**
     * Undocumented function
     *
     * @param integer|null $startTimeStamp
     */
    public function __construct(int $startTimeStamp = null)
    {
        $this->startTimeStamp = $startTimeStamp;
        $this->initStartDate();
    }

    /**
     * Undocumented function
     *
     * @return self
     */
    public function initStartDate(): self
    {
        if (!isset($this->startTimeStamp) && isset($_REQUEST['startDate']))
            $this->startTimeStamp = strtotime(date('Y-m-d', intval($_REQUEST['startDate'])));

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getIntervalWithDayTimeStamps(): array
    {
        return array_keys($this->getIntervalWithDays());
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getIntervalWithDays(): array
    {
        return $this->result ?? $this->result = MainDay::getIntervalFromTimeByDayCount($this->startTimeStamp, static::getIntervalDayCount() - 1);
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public static function getIntervalDayCount(): int
    {
        if (empty($_REQUEST['quarterNumber']) || empty($_REQUEST['quarterYear'])) {
            return WEEK_DAY_COUNT + 1;

        } else {
            return (new Quarter($_REQUEST['quarterNumber']))->getDayCountByYear($_REQUEST['quarterYear']);
        }
    }
}