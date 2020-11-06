<?

class Day
{
    const SECOND_COUNT = DAY_SECOND_COUNT;
    const FORMAT = 'Y-m-d';
    const CALENDAR_FORMAT = 'd.m.Y';

    /**
     * Возвращает массив с данными о днях на конкретный период, длиной равной значению в параметре
     * $nextDayCount, и начинающейся со дня, указанного в значении параметра $startDay.
     * Элементы Возвращаемого значения имеют вид:
     *     <unix-время даты> => <данные>
     * где <данные> это массив с ключами
     *     VALUE. Дата в формате, указанный в константе Day::CALENDAR_FORMAT;
     *     WEEK_DAY_NAME. Название дня недели для даты.
     * 
     * @param string $startDay - начальное число, должно иметь формат Y-m-d
     * @param int $nextDayCount - количество после даты в значении $startDay
     * @return array
     */
    public static function getPeriod(string $startDay, int $nextDayCount)
    {
        global $langValues;

        $currentTime = strtotime($startDay);
        if (!$currentTime) return [];

        $dayFullNames = array_values($langValues['DATE_CHOOOSING']['DAYS']['FULL']);
        $days = [];
        foreach (range($currentTime, $currentTime + self::SECOND_COUNT * $nextDayCount, self::SECOND_COUNT) as $dateValue) {
            $days[$dateValue] = [
                'VALUE' => date(self::CALENDAR_FORMAT, $dateValue),
                'WEEK_DAY_NAME' => $dayFullNames[date('w', $dateValue)]
            ];
        }
        return $days;
    }
};