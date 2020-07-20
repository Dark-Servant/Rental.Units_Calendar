<?

class Technic extends InfoserviceModel
{
    static $has_many = [
        ['contents']
    ];

    const MIN_DEAL_COUNT = 1;
    const MAX_DEAL_COUNT = 3;

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
     * @param array $orders - дополнительные условия для сортировки данных из БД. Если указан
     * параметр с ключом "content", то он будет восприниматься как сортировка для контента, остальные
     * параметры и ключи будут считаться сотрировкой у техники
     * 
     * @return array
     */
    public static function getWithContentsByDayPeriod(array $dayPeriod, array $conditions = [], array $orders = [])
    {
        if (!is_array($dayPeriod) || empty($dayPeriod)) return [];
        $dayTimestamps = array_keys($dayPeriod);

        $contentConditions = self::getWithAddedConditions(
                                    [
                                        '(begin_date <= ?) AND (finish_date >= ?)',
                                        (new DateTime())->setTimestamp(end($dayTimestamps)),
                                        (new DateTime())->setTimestamp(reset($dayTimestamps))
                                    ],
                                    !empty($conditions['content']) && is_array($conditions['content']) ? $conditions['content'] : []
                                );

        $contentOrders = is_string($orders['content']) ? $orders['content'] : '';
        $technics = [];
        foreach (
            self::find(
                'all',
                [
                    'conditions' => self::getWithAddedConditions(
                                        ['is_visibility = 1'],
                                        array_filter($conditions,
                                            function($key) { return !in_array($key, ['content'], true); },
                                            ARRAY_FILTER_USE_KEY
                                        )
                                   ),
                    'order' => implode(
                                    ', ',
                                    array_filter($orders,
                                        function($key) { return !in_array($key, ['content'], true); },
                                        ARRAY_FILTER_USE_KEY
                                    )
                                )
                ]
            ) as $technic 
        ) {
            if ($technic->is_my) {
                $key = 'T' . $technic->id;
                $technics[$key] = [
                    'NAME' => $technic->name,
                    'STATE_NUMBER' => $technic->state_number,
                    'IS_CHOSEN' => false,
                    'CONTENTS' => array_fill_keys($dayTimestamps, false)
                ];
            
            } else {
                $key = 'P' . $technic->partner_id;
                if (!$technics[$key])
                    $technics[$key] = [
                        'NAME' => preg_replace('/\[[^\[\]]+\]/', '', strip_tags($technic->partner_name)),
                        'IS_PARTNER' => true,
                        'IS_CHOSEN' => false,
                        'CONTENTS' => array_fill_keys($dayTimestamps, false)
                    ];
            }
            $dayContents = &$technics[$key]['CONTENTS'];

            foreach (
                Content::find(
                    'all',
                    [
                        'conditions' => self::getWithAddedConditions($contentConditions, ['technic_id' => $technic->id]),
                        'order' => $contentOrders
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
                foreach (
                    range(
                        $content->begin_date->getTimestamp(),
                        $content->finish_date->getTimestamp(),
                        Day::DAY_SECOND_COUNT
                    ) as $dayTimestamp
                ) {
                    if (!isset($dayContents[$dayTimestamp])) continue;

                    if (!isset($dayContents[$dayTimestamp]['STATUS'])) {
                        $dayContents[$dayTimestamp]['STATUS'] = $contentStatus;
                        $dayContents[$dayTimestamp]['STATUS_CLASS'] = Content::CONTENT_DEAL_STATUS[$contentStatus];
                        
                    } elseif ($dayContents[$dayTimestamp]['STATUS'] != $content->status) {
                        $dayContents[$dayTimestamp]['STATUS'] = CONTENT_MANY_DEAL_STATUS;
                        $dayContents[$dayTimestamp]['STATUS_CLASS'] = Content::CONTENT_DEAL_STATUS[CONTENT_MANY_DEAL_STATUS];
                    }
                    ++$dayContents[$dayTimestamp]['DEAL_COUNT'];
                    $dayContents[$dayTimestamp]['IS_ONE'] = $dayContents[$dayTimestamp]['DEAL_COUNT'] == self::MIN_DEAL_COUNT;
                    $dayContents[$dayTimestamp]['VERY_MANY'] = $dayContents[$dayTimestamp]['DEAL_COUNT'] > self::MAX_DEAL_COUNT;
                    $dayContents[$dayTimestamp]['DEALS'][] = [
                        'ID' => $content->id,
                        'DEAL_URL' => $content->deal_url,
                        'RESPONSIBLE_NAME' => $content->responsible->name,
                        'CUSTOMER_NAME' => $content->customer->name,
                        'WORK_ADDRESS' => $content->work_address,
                        'LAST_COMMENT' => ''
                    ];
                }
            }
        }

        return array_values($technics);
    }
};