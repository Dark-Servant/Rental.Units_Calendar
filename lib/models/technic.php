<?
use Frontend\MainTable\RowData\TechnicInterval;

class Technic extends Models\InfoserviceBase
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

    protected static $contentConditions;

    /**
     * Функция-генератор для получения данных по ВИДИМОЙ технике
     * 
     * @param int $startDayTimestamp -
     * @param int $finishDayTimestamp -
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
    protected static function visibilityUnits(int $startDayTimestamp, int $finishDayTimestamp, array $conditions, array $orders)
    {
        self::$contentConditions = self::getWithAddedConditions(
                                        [
                                            '(begin_date <= ?) AND (finish_date >= ?)',
                                            (new DateTime)->setTimestamp($finishDayTimestamp),
                                            (new DateTime)->setTimestamp($startDayTimestamp)
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
                    'order' => 'sort ASC, id ASC'
                ]
            ) as $content
        ) {
            yield $content;
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
     * @param array $dayTimestamps -
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
    public static function getWithContentsByDayPeriod(int $externalUserId, array $dayTimestamps, array $conditions = [], array $orders = [])
    {
        $startDayTimestamp = reset($dayTimestamps);
        $finishDayTimestamp = end($dayTimestamps);

        $result = new TechnicInterval($dayTimestamps);

        foreach (self::visibilityUnits($startDayTimestamp, $finishDayTimestamp, $conditions, $orders) as $technic) {
            $result->addTechnic($technic);
            foreach (self::contentsWithInitedFilter($technic->id) as $content) {
                $result->addToLastTechnicNextContent($content);
            }
        }
        $userId = $externalUserId && !empty($user = Responsible::find_by_external_id($externalUserId))
                ? $user->id : 0;

        if ($userId) $result->loadComments()->prepareReadyDataForUserID($userId);

        return $result->getResult();
    }
};