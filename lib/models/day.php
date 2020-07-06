<?

class Day extends ActiveRecord\Model
{
    const DAY_SECOND_COUNT = 86400;

    static $has_many = [
        ['contents']
    ];

    /**
     * Возвращает массив с данными о днях на конкретный период, длиной равной значению в параметре
     * $nextDayCount, и начинающейся со дня, указанного в значении параметра $startDay.
     * Возвращаемое значение имеет два "ключа":
     *     - timestamps. массив, где "ключ" э то идентификатор значения даты в БД, а "значение" это timestamp-представление даты;
     *     - data. Массив, где "ключ" это timestamp-представление даты, а "значение" это массив с параметрами:
     *         - VALUE. Дата в формате, указанный в константе DAY_CALENDAR_FORMAT;
     *         - WEEK_DAY_NAME. Название дня недели для даты.
     * 
     * @param string $startDay - начальное число, должно иметь формат Y-m-d
     * @param int $nextDayCount - количество после даты в значении $startDay
     * @param array $conditions - дополнительные условия для полученния данных о датах
     * @return array
     */
    public static function getPeriod(string $startDay, int $nextDayCount, array $conditions = [])
    {
        global $langValues;

        $currentTime = strtotime($startDay);
        if (!$currentTime) return [];

        $dayFullNames = array_values($langValues['DATE_CHOOOSING']['DAYS']['FULL']);
        $dayTimestamps = [];
        $days = [];
        foreach (range($currentTime, $currentTime + self::DAY_SECOND_COUNT * $nextDayCount, self::DAY_SECOND_COUNT) as $dateValue) {
            $days[$dateValue] = [
                'VALUE' => date(DAY_CALENDAR_FORMAT, $dateValue),
                'WEEK_DAY_NAME' => $dayFullNames[date('w', $dateValue)]
            ];
        }
        foreach (
            self::find(
                'all',
                [
                    'conditions' => [
                        'VALUE' => array_map(function($date) { return date(DAY_FORMAT, $date); }, array_keys($days))
                    ] + $conditions
                ]
            ) as $day
        ) {
            $dayTimestamps[$day->id] = $day->value->getTimestamp();
        }

        return [
            'timestamps' => $dayTimestamps,
            'data' => $days
        ];
    }
};