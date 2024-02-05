<?

/**
 * Undocumented class
 */
class Quarter
{
    protected $number;

    /**
     * Undocumented function
     *
     * @param integer $number
     */
    public function __construct(int $number)
    {
        $this->number = static::getCorrectedNumber($number);
    }

    /**
     * Undocumented function
     *
     * @param integer $number
     * @return integer
     */
    public static function getCorrectedNumber(int $number): int
    {
        $result = $number % 5;
        return $number < 1 ? 1 : $number;
    }

    /**
     * Undocumented function
     *
     * @param integer $year
     * @return integer
     */
    public function getDayCountByYear(int $year): int
    {
        if ($this->number > 2) {
            return 92;

        // Во 2м квартале столько же дней, как и в 1м, если год высокосный
        } elseif (($this->number > 1) || !(intval($year) & 3)) {
            return 91;

        } else {
            return 90;
        }
    }
}