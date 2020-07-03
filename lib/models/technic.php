<?

class Technic extends ActiveRecord\Model
{
    static $has_many = [
        ['contents']
    ];

    /**
     * Возвращает массив техники с привязынными к каждой контенту. Возвращаемое значение представляет
     * из себя массив, где каждый элемент хранит следующие данные:
     *     - ID. Идентификатор техники;
     *     - NAME. Название техники;
     *     - IS_MY. Флаг о том, своя ли техника;
     *     - CONTENTS. Массив с привязынным к технике контентом, состоит из элементов, где каждый либо равен FALSE, т.е. нет контента
     *     для техники на конкретную дату, чей порядковый номер во входном параметре $dayPeriod['data'] будет таким же, либо каждый
     *     элемент имеет параметры:
     *         - STATUS. Общий код статуса контента;
     *         - STATUS_CLASS. Имя общего класса статуса;
     *         - DEAL_COUNT. Количество сделок в контенте;
     *         - DEAL_IS_ONE. Флаг с пометкой, одна ли сделка в контенте (альтернатива это проверка равенства DEAL_COUNT с единицей)
     *         - DEALS. Массив со сделками, где у каждой сделки параметры:
     *             - ID. Идентификатор контента;
     *             - DEAL_URL. Адрес на сделку;
     *             - RESPONSIBLE_NAME. Имя исполнителя;
     *             - CUSTOMER_NAME. Имя заказчика;
     *             - WORK_ADDRESS. Рабочик адрес
     * 
     * @param array $dayPeriod - данные, полученные от метода getPeriod у модели Day
     * @param array $conditions - дополнительные условия для фильтра данных из БД. Если указан
     * параметр с ключом "content", то он будет восприниматься как фильтр для контента, остальные
     * параметры и ключи будут считаться как фильтр для техники
     * 
     * @return array
     */
    public static function getWithContentsByDayPeriod(array $dayPeriod, array $conditions = [])
    {
        $dayTimestamps = array_keys(is_array($dayPeriod['data']) ? $dayPeriod['data'] : []);
        if (empty($dayTimestamps)) return [];

        $dayIds = array_keys(is_array($dayPeriod['timestamps']) ? $dayPeriod['timestamps'] : []);
        $contentConditions = is_array($conditions['content']) ? $conditions['content'] : [];
        return array_map(
                function($technic) use($dayPeriod, $dayTimestamps, $dayIds, $contentConditions) {
                    $dayContents = array_fill_keys($dayTimestamps, false);
                    foreach (
                        Content::find(
                            'all',
                            [
                                'conditions' => [
                                    'technic_id' => $technic->id,
                                    'day_id' => $dayIds
                                ] + $contentConditions
                            ]
                        ) as $content
                    ) {
                        if ($content->is_closed) {
                            $contentStatus = CONTENT_CLOSED_DEAL_STATUS;

                        } else {
                            $contentStatus = $content->status >= CONTENT_MAX_DEAL_STATUS
                                           ? CONTENT_MAX_DEAL_STATUS
                                           : $content->status;
                        }
                        $dayTimestamp = $dayPeriod['timestamps'][$content->day_id];
                        if (!$dayContents[$dayTimestamp]) {
                            $dayContents[$dayTimestamp]['STATUS'] = $contentStatus;
                            $dayContents[$dayTimestamp]['STATUS_CLASS'] = CONTENT_DEAL_STATUS[$contentStatus];
                            
                        } elseif ($dayContents[$dayTimestamp]['STATUS'] != $content->status) {
                            $dayContents[$dayTimestamp]['STATUS'] = CONTENT_MANY_DEAL_STATUS;
                            $dayContents[$dayTimestamp]['STATUS_CLASS'] = CONTENT_DEAL_STATUS[CONTENT_MANY_DEAL_STATUS];
                        }
                        ++$dayContents[$dayTimestamp]['DEAL_COUNT'];
                        $dayContents[$dayTimestamp]['DEAL_IS_ONE'] = $dayContents[$dayTimestamp]['DEAL_COUNT'] == 1;
                        $dayContents[$dayTimestamp]['DEALS'][] = [
                            'ID' => $content->id,
                            'DEAL_URL' => strtr(DEAL_URL_TEMPLATE, ['#ID#' => $content->deal_id]),
                            'RESPONSIBLE_NAME' => $content->responsible->name,
                            'CUSTOMER_NAME' => $content->customer->name,
                            'WORK_ADDRESS' => $content->work_address,
                        ];
                    }
                    return [
                        'ID' => $technic->id,
                        'NAME' => $technic->name,
                        'IS_MY' => $technic->is_my,
                        'CONTENTS' => array_values($dayContents)
                    ];
                },
                self::find(
                    'all',
                    [
                        'conditions' => array_merge(
                                            array_filter($conditions,
                                                function($key) { return !in_array($key, ['content']); },
                                                ARRAY_FILTER_USE_KEY
                                            ),
                                            ['visibility <> 0']
                                        )
                    ]
                )
            );
    }
};