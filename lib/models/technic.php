<?

class Technic extends InfoserviceModel
{
    static $belongs_to = [
        ['partner'],
    ];

    static $has_many = [
        ['contents']
    ];

    const MIN_DEAL_COUNT = 1;
    const MAX_DEAL_COUNT = 3;

    protected static $contentConditions;
    protected static $contentOrders;

    /**
     * По данным экземпляра класса Technic добавляет элемент с кратким описанием
     * техники или партнера в зависимости то того, отмечена ли техника как "своя".
     * Если техника "своя", то идет сохранение ее краткой информации в параметр
     * $technics.
     * Не зависимо от того, "своя" или нет техника, у кратких данных по технике или
     * партнеру обязательно будет параметр CONTENTS, в котором хранится массив с
     * краткой информацией о контенте за каждую технику или партнера. Метод вернет
     * результат в виде массива как ссылку на параметр CONTENTS, к которому можно
     * сразу привязаться и изменять данные контента, относящиеся к краткой информации,
     * созданной по переданному экземпляру в $technic
     * 
     * @param $technic - экземпляр класса Technic
     * @param array $dayTimestamps - массив unix-меток времени
     * @param array &$technics - массив для сохранения результата по технике
     * @param array &$partners - массив для сохранения результата по партнерам
     * @return &array
     */
    protected static function &initUnitAndGetContents($technic, array $dayTimestamps, array&$technics, array&$partners)
    {
        if ($technic->is_my) {
            $technics[$technic->id] = [
                'ID' => $technic->id,
                'NAME' => $technic->name,
                'IS_PARTNER' => false,
                'STATE_NUMBER' => $technic->state_number,
                'IS_CHOSEN' => false,
                'CONTENTS' => array_fill_keys($dayTimestamps, false)
            ];
            return $technics[$technic->id]['CONTENTS'];
        
        } else {
            if (!$partners[$technic->partner_id]) {
                $partners[$technic->partner_id] = [
                    'ID' => $technic->partner_id,
                    'IS_CHOSEN' => false,
                    'CONTENTS' => array_fill_keys($dayTimestamps, false)
                ];
            }

            return $partners[$technic->partner_id]['CONTENTS'];
        }
    }

    /**
     * Функция-генератор для получения данных по ВИДИМОЙ технике
     * 
     * @param array $dayTimestamps - массив unix-меток времени
     * @param array $conditions - дополнительный фильтр по технике. Может содержать данные
     * для фильтра по контенту. Для этого надо указать массив с фильтром к контенту в значении
     * под ключом с именем "content". Фильтр для контента будет сохранен, чтобы при
     * работе с методом как генератором он использовался в методе contentsWithInitedFilter,
     * после этот фильтр будет сброшен
     * 
     * @param array $orders - данные по сортировке. Если указан ключ "content", то значение
     * под этим ключом будет установлено для сортировки данных, получаемых через метод
     * contentsWithInitedFilter. Только будет использоваться эта сортировка по контенту,
     * если в процессе работы с методом как генератором, будет использоваться и метод
     * contentsWithInitedFilter. После работы метода visibilityUnits данные о сортировке
     * контента сбрасываются
     * 
     * @yield
     */
    protected static function visibilityUnits(array $dayTimestamps, array $conditions, array $orders)
    {
        self::$contentConditions = self::getWithAddedConditions(
                                        [
                                            '(begin_date <= ?) AND (finish_date >= ?)',
                                            (new DateTime)->setTimestamp(end($dayTimestamps)),
                                            (new DateTime)->setTimestamp(reset($dayTimestamps))
                                        ],
                                        !empty($conditions['content']) && is_array($conditions['content']) ? $conditions['content'] : []
                                    );

        self::$contentOrders = is_string($orders['content']) ? $orders['content'] : '';
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
            yield $technic;
        }
        self::$contentConditions = self::$contentOrders = null;
    }

    /**
     * Функция-генератор для получения конкретной техники данных по контенту, используя
     * сохраненный в процессе работы метода visibilityUnits дополнительные фильтр и
     * сортировку для контента
     * 
     * @param int $technicId - идентификатор техники
     * 
     * @yield
     */
    protected static function contentsWithInitedFilter(int $technicId)
    {
        if ($technicId < 1) return;

        foreach (
            Content::find(
                'all',
                [
                    'conditions' => self::getWithAddedConditions(self::$contentConditions ?? [], ['technic_id' => $technicId]),
                    'order' => self::$contentOrders ?? ''
                ]
            ) as $content
        ) {
            yield $content;
        }
    }

    /**
     * Устанавливает для конкретного пользователя, если он указан, в переданных
     * данных по технике и партнерах отметку в параметре IS_CHOSEN, что техника
     * была избрана пользователем
     * 
     * @param int $externalUserId - идентификатор пользователя на портале
     * @param array &$technics - массив заранее подговленных данных техники
     * @param array &$partners - массив заранее подговленных данных партнеров
     * @return void
     */
    protected static function setChosenStatus(int $externalUserId, array&$technics, array&$partners)
    {
        if (
            !$externalUserId || empty($user = Responsible::find_by_external_id($externalUserId))
            || (empty($technics) && empty($partners))
        ) return;

        $values = [];
        $conditions = '';
        foreach ([array_keys($technics), array_keys($partners)] as $number => $ids) {
            if (empty($ids)) continue;

            $conditions .= ($conditions ? ' OR ' : '') . '((is_partner = ' . $number . ') AND (entity_id IN (?)))';
            $values[] = $ids;
        }
        $conditions = '(' . $conditions . ') AND (is_active = 1) AND (user_id = ?)';
        $values[] = $user->id;

        foreach (
            ChosenTechnics::find('all', ['conditions' => array_merge([$conditions], $values)]) as $chosen
        ) {
            if ($chosen->is_partner) {
                $partners[$chosen->entity_id]['IS_CHOSEN'] = true;

            } else {
                $technics[$chosen->entity_id]['IS_CHOSEN'] = true;
            }
        }
    }

    /**
     * Возвращает массив с краткой информацией о технике и партнерах с привязынными к каждому из них списка контента.
     * Возвращаемое значение представляет из себя массив, где каждый элемент хранит следующие данные:
     *     - ID. Идентификатор техники;
     *     - NAME. Название техники;
     *     - IS_CHOSEN. Флаг о том, была ли избрана техника или партнер текущим пользователем портала
     *     - IS_PARTNER. Флаг о том, является ли это просто техникой или же партнер
     *     - STATE_NUMBER. Гос. номер техники, есть, если IS_PARTNER равен false
     *     - CONTENTS. Массив с привязынным к данным техники или партнера элементов контента, где каждый элемент
     *     либо равен false, т.е. нет контента для данных техники или партнера на конкретную дату. Каждый не равный
     *     false элемент массива хранит краткую информацию о контенте, имеет следующие параметры:
     *         - STATUS. Общий код статуса контента;
     *         - STATUS_CLASS. Имя общего класса статуса;
     *         - DEAL_COUNT. Количество сделок в контенте;
     *         - DEAL_IS_ONE. Флаг с пометкой, одна ли сделка в контенте (альтернатива это проверка равенства DEAL_COUNT с единицей)
     *         - DEALS. Массив со сделками, где у каждой сделки параметры:
     *             - ID. Идентификатор контента;
     *             - DEAL_URL. Адрес на сделку;
     *             - RESPONSIBLE_NAME. Имя исполнителя;
     *             - CUSTOMER_NAME. Имя заказчика;
     *             - WORK_ADDRESS. Рабочик адрес;
     *             - LAST_COMMENT. последний комментарий.
     * 
     * @param int $externalUserId - идентификатор пользователя на портале
     * @param array $dayPeriod - данные, полученные от метода getPeriod у класса Day
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
    public static function getWithContentsByDayPeriod(int $externalUserId, array $dayPeriod, array $conditions = [], array $orders = [])
    {
        if (!is_array($dayPeriod) || empty($dayPeriod)) return [];
        $dayTimestamps = array_keys($dayPeriod);

        $technics = [];
        $partners = [];
        foreach (self::visibilityUnits($dayTimestamps, $conditions, $orders) as $technic) {
            $dayContents = &self::initUnitAndGetContents($technic, $dayTimestamps, $technics, $partners);

            foreach (self::contentsWithInitedFilter($technic->id) as $content) {
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
                        Day::SECOND_COUNT
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
                    $dealUnit = [
                        'ID' => $content->id,
                        'DEAL_URL' => $content->deal_url,
                        'RESPONSIBLE_NAME' => $content->responsible->name,
                        'CUSTOMER_NAME' => $content->customer->name,
                        'WORK_ADDRESS' => $content->work_address,
                        'LAST_COMMENT' => ''
                    ];
                    if (!$technic->is_my)
                        $dealUnit += [
                            'TECHNIC_ID' => $technic->id,
                            'TECHNIC_NAME' => $technic->name
                        ];

                    $dayContents[$dayTimestamp]['DEALS'][] = $dealUnit;
                }
            }
        }

        self::setChosenStatus($externalUserId, $technics, $partners);

        $technicResult = array_values($technics);
        if (!empty($partners)) {
            foreach (Partner::find('all', ['conditions' => ['id' => array_keys($partners)], 'order' => 'name ASC']) as $partner) {
                $technicResult[] = [
                    'NAME' => $partner->name,
                    'IS_PARTNER' => true
                ] + $partners[$partner->id];
            }
        }
        return $technicResult;
    }
};