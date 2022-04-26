<?

class Content extends InfoserviceModel
{
    static $belongs_to = [
        ['technic'],
        ['responsible'],
        ['customer'],
    ];

    static $has_many = [
        ['comments']
    ];

    /**
     * Информация о классах контента. Используется при выводе в календаре, 
     * чтобы окрасить в конкретный цвет.
     * В параметре status контента хранится число, которое указывает на
     * порядковый номер класса из этой константы, но, если оно больше,
     * чем порядковый номер элемента final, то таким статус и остается, 
     * т.е. final
     */
    const CONTENT_DEAL_STATUS = [
        'waiting', 'process', 'final', 'closed', 'many', 'repair'
    ];

    /**
     * Регулярные выражения для установки по текстовому значению параметра status
     * у контента реального его числового значения, которое будет порядковым номером
     * подошедшего регулярного выражения. Это значение так же будет указывать на
     * порядковый номер элемента из константы CONTENT_DEAL_STATUS. Если ни одно из
     * регулярных выражение не определило статус, то статус будет равен длине массива, т.е,
     * как указано в комментарии к константе CONTENT_DEAL_STATUS, затем восприниматься
     * как final
     */
    const CONTENT_DEAL_STATUS_REGEX = [
        '/\b(?:нов[аяйоые]+|резерв)(?: +[\W\w]*)?$/iu',
        '/\b(?:проведение|в +процессе)(?: +[\W\w]*)?$/iu',
        '/\b(?:финал[ьнаяйоые]*|закрыт[аяыйое]*|заверш[иаолуеють]+)\b/iu'
    ];

    /**
     * Для проверки не находится ли контент в "ремонте", что влияет на общий статус
     * контента в календаре если так и окажется
     */
    const CONTENT_REPAIR_STATUS_REGEX = '/\bремонт +техники\b/iu';

    /**
     * Обработчик изменения параметра status. Если параметр будет иметь строковое значение,
     * то благодаря константе CONTENT_DEAL_STATUS_REGEX будет заменено на числовое
     * 
     * @param $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    protected function correctStatusValue($name, &$value)
    {
        if (strtolower($name) != 'status') return false;

        if (is_string($value) && preg_match('/^\d+$/', $value))
            $value = intval($value);

        $regexValues = self::CONTENT_DEAL_STATUS_REGEX;
        $regexValueCount = count($regexValues);
        if (is_numeric($value)) {
            if ($value > $regexValueCount) {
                $value = $regexValueCount;

            } elseif ($value < 0) {
                $value = 0;
            }

        } else {
            $newValue = null;
            $value = strval($value);
            foreach ($regexValues as $newValueNumber => $statusRegex) {
                if (preg_match($statusRegex, $value)) {
                    $newValue = $newValueNumber;
                    break;
                }
            }
            $value = isset($newValue) ? $newValue : $regexValueCount;
        }

        return true;
    }

    /**
     * Возвращает массив с данными контента, которые надо использовать
     * при выводе в календаре
     * 
     * @return array
     */
    public function getCellData()
    {
        global $langValues;
        $dealURL = $this->deal_url;
        $this->correctURLValue('deal_url', $dealURL);

        $data = [
            'ID' => $this->id,
            'DEAL_URL' => $dealURL,
            'RESPONSIBLE_NAME' => $this->responsible->name,
            'CUSTOMER_NAME' => $this->customer->name,
            'WORK_ADDRESS' => $this->work_address
        ];
        if (!$this->technic->is_my)
            $data += [
                'TECHNIC_ID' => $this->technic_id,
                'TECHNIC_NAME' => $this->technic->name
            ];

        if (preg_match(self::CONTENT_REPAIR_STATUS_REGEX, $data['WORK_ADDRESS'])) {
            $data['DEAL_URL'] = 
            $data['RESPONSIBLE_NAME'] =
            $data['WORK_ADDRESS'] = '';
            $data['CUSTOMER_NAME'] = $langValues['CONTENT_REPAIR_STATUS_TITLE'];
            $data['IS_REPAIR'] = true;
        }

        return $data;
    }

    /**
     * Возвращает массив комментариев, которые привязаны к контенту, но таковыми быть
     * не должны из-за связи с другой техникой или датой вывода в календаре, которая не
     * попадает в интервал между начальной и конечной датами контента.
     * Возвращаемый результат будет иметь вид
     *     [
     *         <ID техники> => [
     *             <дата вывода как ГГГГ-ММ-ДД> => [<ID комментария-1>, <ID комментария-2>, ..., <ID комментария-N>]
     *         ],
     *         ...
     *     ]
     *
     * @param bool $throwAll - указывает брать ли все комментарии, по-умолчанию берутся те, что
     * вне рамок дат контента
     * 
     * @return array
     */
    protected function getThrownComments(bool $throwAll = false)
    {
        $commentIds = array_map(
                            function($comment) { return $comment->id; },
                            $this->comments
                        );
        if (empty($commentIds)) return [];

        if ($throwAll) {
            $comments = Comment::all(['id' => $commentIds]);

        } else {
            $comments = Comment::all([
                                'conditions' => [
                                    '(id IN (?)) AND ((content_date < ?) OR (content_date > ?))',
                                    $commentIds,
                                    $this->begin_date->format(Day::FORMAT),
                                    $this->finish_date->format(Day::FORMAT)
                                ]
                            ]);

        }
        $commentIds = [];
        foreach ($comments as $comment) {
            $commentIds[$comment->technic_id][$comment->content_date->format(Day::FORMAT)][] = $comment->id;
        }
        return $commentIds;
    }

    /**
     * Возвращает массив комментариев, которые в силу изменения данных текущего контента
     * не могут больше принадлежать контенту. Идентификаторы комментариев в возвращаемом
     * результате разбиты на категории:
     *     contentCommentIds - идентификаторы комментариев, идентификаторы контентов и идентификаторы техники
     *     с которыми надо связать указанные комментарии. В результате группы идентификаторов комментариев
     *     сгруппированы по элементам, где в каждом элементе
     *         [
     *             ids => [<ID комментария 1>, <ID комментария 2>, ..., <ID комментария N>],
     *             contentId => <ID нового контента>,
     *             technicId => <ID новой техники>
     *         ],
     *         ...
     *     
     *     zeroCommentIds - массив идентификаторов комментариев, у которых надо обнулить связь с контентом
     *
     * @param bool $throwAll - указывает на то, что надо все текущие комментарии контента бросить
     * @return array
     */
    protected function getThrownCommentsWithNewRoles(bool $throwAll = false)
    {
        $contentCommentIds = [];
        $zeroCommentIds = [];
        foreach ($this->getThrownComments($throwAll) as $technicId => $comments) {
            $technic = Technic::find($technicId);
            $technicIds = $technic->partner
                        ? array_map(
                                function($technic) { return $technic->id; },
                                Technic::all(['partner_id' => $technic->partner_id])
                            )
                        : [$technicId];
            $dates = array_keys($comments);
            foreach (
                self::all([
                    'conditions' => [
                        '(id <> ?) AND (technic_id IN (?)) AND (begin_date <= ?) AND (finish_date >= ?)',

                        $this->id, $technicIds, max($dates), min($dates)
                    ],
                    'order' => 'id asc'
                ]) as $content
            ) {
                $contentBeginDate = $content->begin_date->format(Day::FORMAT);
                $contentFinishDate = $content->finish_date->format(Day::FORMAT);
                $newDates = [];
                $commentIds = [];
                foreach ($dates as $date) {
                    if (($date < $contentBeginDate) || ($date > $contentFinishDate)) {
                        $newDates[] = $date;

                    } else {
                        $commentIds = array_merge($commentIds, $comments[$date]);
                    }
                }
                $contentCommentIds[] = [
                    'ids' => $commentIds,
                    'contentId' => $content->id,
                    'technicId' => $content->technic_id
                ];
                $dates = $newDates;
            }
            foreach ($dates as $date) {
                $zeroCommentIds = array_merge($zeroCommentIds, $comments[$date]);
            }
        }
        return ['contentCommentIds' => $contentCommentIds, 'zeroCommentIds' => $zeroCommentIds];
    }

    /**
     * После изменения данных контента может получиться так, что некоторые комментарии перестанут
     * принадлежать контенту, их надо отдать другому контенту или обнулить связь с любым контентом.
     * Так же после изменения данных у контента могут появиться новые комментарии, которые раньше
     * не были связаны ни с одним контентом и находятся там, куда стал передвинут контент
     *
     * @param bool $throwAll - указывает на то, что надо все текущие комментарии контента бросить,
     * по-умолчанию бросает только те, у которых дата не совпадает с интервалом контента
     * 
     * @param bool $checkNew - указывает на то, что надо поискать новые комментарии
     * @return void
     */
    protected function correctOldComment(bool $throwAll = false, bool $checkNew = true)
    {
        if (!$this->id) return;

        $thrownComments = $this->getThrownCommentsWithNewRoles($throwAll);

        foreach ($thrownComments['contentCommentIds'] as $comment) {
            Comment::update_all([
                'set' => [
                    'content_id' => $comment['contentId'],
                    'technic_id' => $comment['technicId']
                ],
                'conditions' => ['id' => $comment['ids']]
            ]);
        }

        if (!empty($thrownComments['zeroCommentIds']))
            Comment::update_all(['set' => ['content_id' => 0], 'conditions' => ['id' => $thrownComments['zeroCommentIds']]]);

        if (!$checkNew) return;

        $partner = $this->technic ? $this->technic->partner : null;
        $technicIds = $partner
                    ? array_map(
                            function($technic) { return $technic->id; },
                            $partner->technics
                        )
                    : [$this->technic_id];
        $newCommentIds = array_map(
                            function($comment) { return $comment->id; },
                            Comment::all([
                                'conditions' => [
                                    '(technic_id IN (?)) AND (content_id = 0) AND (content_date >= ?) AND (content_date <= ?)',
                                    $technicIds,
                                    $this->begin_date->format(Day::FORMAT),
                                    $this->finish_date->format(Day::FORMAT)
                                ]
                            ])
                        );
        if (!empty($newCommentIds))
            Comment::update_all(['set' => ['content_id' => $this->id], 'conditions' => ['id' => $newCommentIds]]);
    }

    /**
     * Обновленный метод сохранения данных в БД
     *
     * @param $validate - параметр для родительского метода
     * @return mixed
     */
    public function save($validate = true)
    {
        $result = parent::save($validate);
        $this->correctOldComment();
        return $result;
    }

    /**
     * Обновленный метод удаления данных в БД
     * 
     * @return mixed
     */
    public function delete()
    {
        $this->correctOldComment(true, false);
        return parent::delete();
    }
};