<?

class Technic extends InfoserviceModel
{
    static $belongs_to = [
        ['partner'],
    ];

    static $has_many = [
        ['contents'],
        ['comments'],
        ['userchoice', 'foreign_key' => 'entity_id', 'class_name' => 'ChosenTechnic', 'conditions' => 'is_partner = "0"'],
    ];
    const CHILD_NAMES_FOR_DELETING = ['contents', 'comments', 'userchoice'];

    const MIN_DEAL_COUNT = 1;
    const MAX_DEAL_COUNT = 3;

    protected static $contentConditions;

    /**
     * По данным экземпляра класса Technic делает инициализацию элемента с кратким
     * описанием техники или партнера в зависимости то того, отмечена ли техника
     * как "своя". Если техника "своя", то идет сохранение ее краткой информации в
     * параметр $technics.
     * Не зависимо от того, "своя" или нет техника, у кратких данных по технике или
     * партнеру обязательно будет параметр CONTENTS, в котором хранится массив с
     * краткой информацией о контенте за каждую технику или партнера. Метод вернет
     * результат как ссылку на краткое описание, где надо использовать параметр CONTENTS,
     * чтобы изменять данные контента
     * 
     * @param $technic - экземпляр класса Technic
     * @param array $dayTimestamps - массив unix-меток времени
     * @param array &$technics - массив для сохранения результата по технике
     * @param array &$partners - массив для сохранения результата по партнерам
     * @return &array
     */
    protected static function &getInitedUnitWithContents($technic, array $dayTimestamps, array&$technics, array&$partners)
    {
        if ($technic->is_my) {
            $technics[$technic->id] = [
                'ID' => $technic->id,
                'NAME' => $technic->name,
                'EXTERNAL_ID' => $technic->external_id,
                'IS_PARTNER' => false,
                'STATE_NUMBER' => $technic->state_number,
                'IS_CHOSEN' => false,
                'CONTENTS' => array_fill_keys($dayTimestamps, false)
            ];
            return $technics[$technic->id];
        
        } else {
            if (!$partners[$technic->partner_id]) {
                $partners[$technic->partner_id] = [
                    'ID' => $technic->partner_id,
                    'EXTERNAL_ID' => $technic->external_id,
                    'IS_PARTNER' => true,
                    'IS_CHOSEN' => false,
                    'CONTENTS' => array_fill_keys($dayTimestamps, false)
                ];
            }

            return $partners[$technic->partner_id];
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
     * @param array $orders - данные по сортировке
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
                                        is_array($conditions['content']) ? $conditions['content'] : []
                                    );
        foreach (
            self::all(
                [
                    'conditions' => self::getWithAddedConditions(
                                        ['is_visibility = 1'],
                                        array_filter($conditions,
                                            function($key) { return !in_array($key, ['content'], true); },
                                            ARRAY_FILTER_USE_KEY
                                        )
                                    ),
                    'order' => implode(', ', $orders)
                ]
            ) as $technic 
        ) {
            yield $technic;
        }
        self::$contentConditions = null;
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
            Content::all(
                [
                    'conditions' => self::getWithAddedConditions(self::$contentConditions ?? [], ['technic_id' => $technicId]),
                    'order' => 'sort ASC'
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
     * @param int $userId - идентификатор пользователя
     * @param array &$technics - массив заранее подговленных данных техники
     * @param array &$partners - массив заранее подговленных данных партнеров
     * @return void
     */
    protected static function setChosenStatus(int $userId, array&$technics, array&$partners)
    {
        if (empty($technics) && empty($partners)) return;

        $values = [];
        $conditions = '';
        foreach ([array_keys($technics), array_keys($partners)] as $number => $ids) {
            if (empty($ids)) continue;

            $conditions .= ($conditions ? ' OR ' : '') . '((is_partner = ' . $number . ') AND (entity_id IN (?)))';
            $values[] = $ids;
        }
        $conditions = '(' . $conditions . ') AND (is_active = 1) AND (user_id = ?)';
        $values[] = $userId;

        foreach (
            ChosenTechnic::all(['conditions' => array_merge([$conditions], $values)]) as $chosen
        ) {
            if ($chosen->is_partner) {
                $partners[$chosen->entity_id]['IS_CHOSEN'] = true;

            } else {
                $technics[$chosen->entity_id]['IS_CHOSEN'] = true;
            }
        }
    }

    /**
     * Загружает данные комментариев к каждой техники или парнерам
     * 
     * @param array $technicPartners - массив связей техники и парнеров. В "ключе" указан идентификатор техники,
     * а в "значении" идентификатор партнера или нуль, если техника сама по себе
     *
     * @param int $userId - идентификатор пользователя
     * @param array $dayTimestamps - массив unix-меток времени от меньшего к большему
     * @param array &$technics - массив с загруженными данными единиц техники
     * @param array &$partners - массив с загруженными данными парнеров
     * 
     * @return void
     */
    protected static function loadComments(int $userId, array $technicPartners, array $dayTimestamps, array&$technics, array&$partners)
    {
        $filter = [
            '(technic_id IN (?)) AND (content_date >= ?) AND (content_date <= ?)',
            array_keys($technicPartners) ?: null,
            (new DateTime)->setTimestamp(reset($dayTimestamps)),
            (new DateTime)->setTimestamp(end($dayTimestamps)),
        ];

        $comments = [];
        foreach (Comment::all(['conditions' => $filter, 'order' => 'id asc']) as $comment) {
            $partnerId = $technicPartners[$comment->technic_id];
            if ($partnerId) {
                $technicRow = &$partners[$partnerId];

            } else {
                $technicRow = &$technics[$comment->technic_id];
            }
            $comments[$comment->id] = $comment->getData();
            $dayTimestamp = $comment->content_date->getTimestamp();
            $technicRow['COMMENTS'][$dayTimestamp][] = &$comments[$comment->id];
            if ($comment->duty_status)
                $technicRow['CONTENTS'][$dayTimestamp]['STATUS_CLASS'] = Comment::DUTY_STATUS[$comment->duty_status];
        }

        if (empty($comments)) return;
        foreach (
            ReadCommentMark::all([
                'user_id' => $userId,
                'comment_id' => array_keys($comments)
            ]) as $mark
        ) {
            $comments[$mark->comment_id]['READ'] = true;
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

        $technicPartners = [];
        $technics = [];
        $partners = [];

        /**
         * dayDealNames нужна, чтобы в ячейках, куда попадают несколько элементов из контента, не выводились
         * более одного раза те элементы, у которых одинаковые ссылки или имена, если ссылок нет
         */
        $dayDealNames = [];
        foreach (self::visibilityUnits($dayTimestamps, $conditions, $orders) as $technic) {
            $technicData = &self::getInitedUnitWithContents($technic, $dayTimestamps, $technics, $partners);
            $dayContents = &$technicData['CONTENTS'];
            $dayDealNamesCode = ($technicData['IS_PARTNER'] ? 'P' : 'T') . $technicData['ID'];
            $technicPartners[$technic->id] = $technic->partner_id;

            foreach (self::contentsWithInitedFilter($technic->id) as $content) {
                $cellData = $content->getCellData();
                /**
                 * Устанавливаем параметр $dealName, чтобы потом проверить не выводился ли этот контент
                 * в той же ячейке
                 */
                if (empty($cellData['DEAL_URL']) || !preg_match('/\/(\d+)/', $cellData['DEAL_URL'], $URLParts)) {
                    $dealName = 'n:' . trim(strtolower($cellData['CUSTOMER_NAME']));

                } else {
                    $dealName = 'u:' . $URLParts[1];
                }
                
                if ($cellData['IS_REPAIR']) {
                    $contentStatus = CONTENT_REPAIR_DEAL_STATUS;

                } elseif ($content->is_closed) {
                    $contentStatus = CONTENT_CLOSED_DEAL_STATUS;

                } else {
                    $contentStatus = $content->status >= CONTENT_MAX_DEAL_STATUS
                                   ? CONTENT_MAX_DEAL_STATUS
                                   : $content->status;
                }

                foreach (
                    range(
                        $content->begin_date->getTimestamp(), $content->finish_date->getTimestamp(), Day::SECOND_COUNT
                    ) as $dayTimestamp
                ) {
                    if (!isset($dayContents[$dayTimestamp])) continue;

                    /**
                     * Проверка на то, был ли конкретный контент выведен в текущей ячейке. Если да, то он уже отметился
                     * для текущей техники (партнера) и даты, поэтому устанавливается, что его не надо выводить.
                     */
                    $cellData['CELL_SHOWING'] = empty($dayDealNames[$dayDealNamesCode][$dayTimestamp][$dealName]);
                    $dayDealNames[$dayDealNamesCode][$dayTimestamp][$dealName] = true;

                    /**
                     * Из-за бага с дублированнием контента, когда действие БП запускается параллельно несколько раз
                     * при, возможно, нескольких раз обращений из шаблона БП, приходится делать проверку статуса контента
                     * только для того контента, который еще не отметился для текущей даты и техники
                     */
                    if ($cellData['CELL_SHOWING']) {
                        if (
                            !isset($dayContents[$dayTimestamp]['STATUS'])
                            || ($contentStatus == CONTENT_REPAIR_DEAL_STATUS)
                        ) {
                            $dayContents[$dayTimestamp]['STATUS'] = $contentStatus;
                            $dayContents[$dayTimestamp]['STATUS_CLASS'] = Content::CONTENT_DEAL_STATUS[$contentStatus];
                            
                        } elseif (
                            ($dayContents[$dayTimestamp]['STATUS'] != CONTENT_REPAIR_DEAL_STATUS)
                            && ($dayContents[$dayTimestamp]['STATUS'] != $contentStatus)
                        ) {
                            $dayContents[$dayTimestamp]['STATUS'] = CONTENT_MANY_DEAL_STATUS;
                            $dayContents[$dayTimestamp]['STATUS_CLASS'] = Content::CONTENT_DEAL_STATUS[CONTENT_MANY_DEAL_STATUS];
                        }

                        ++$dayContents[$dayTimestamp]['DEAL_COUNT'];
                        $dayContents[$dayTimestamp]['IS_ONE'] = $dayContents[$dayTimestamp]['DEAL_COUNT'] == self::MIN_DEAL_COUNT;
                        $dayContents[$dayTimestamp]['VERY_MANY'] = $dayContents[$dayTimestamp]['DEAL_COUNT'] > self::MAX_DEAL_COUNT;
                    }

                    if (!isset($dayContents[$dayTimestamp]['DEALS']))
                        $dayContents[$dayTimestamp]['DEALS'] = [];

                    // необходимо, чтобы контент на ремонте был всегда в начале списка
                    if ($cellData['IS_REPAIR']) {
                        array_unshift($dayContents[$dayTimestamp]['DEALS'], $cellData);

                    } else {
                        $dayContents[$dayTimestamp]['DEALS'][] = $cellData;
                    }
                }
            }
        }
        $userId = $externalUserId && !empty($user = Responsible::find_by_external_id($externalUserId))
                ? $user->id : 0;
        if ($userId) self::setChosenStatus($userId, $technics, $partners);
        self::loadComments($userId, $technicPartners, $dayTimestamps, $technics, $partners);

        $technicResult = array_values($technics);
        if (!empty($partners)) {
            foreach (Partner::all(['conditions' => ['id' => array_keys($partners)], 'order' => 'name ASC']) as $partner) {
                $technicResult[] = [
                    'NAME' => $partner->name,
                    'IS_PARTNER' => true
                ] + $partners[$partner->id];
            }
        }
        return $technicResult;
    }
};