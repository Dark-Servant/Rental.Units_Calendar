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
        '/\b(?:проведение|в +процессе|финальный +счет)(?: +[\W\w]*)?$/iu',
        '/\b(?:финал[ьнаяйоые]*|закрыт[аяыйое]*|заверш[иаолуеють]+)\b/iu'
    ];

    /**
     * Для проверки не находится ли контент в "ремонте", что влияет на общий статус
     * контента в календаре если так и окажется
     */
    const CONTENT_REPAIR_STATUS_REGEX = '/\bремонт +техники\b/iu';

    /**
     * 
     */
    const MIN_DEAL_COUNT = 1;
    const MAX_DEAL_COUNT = 3;

    /**
     * Обработчик изменения параметра status. Если параметр будет иметь строковое значение,
     * то благодаря константе CONTENT_DEAL_STATUS_REGEX будет заменено на числовое
     * 
     * @param $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    public static function correctStatusValue($name, &$value): bool
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
     * Undocumented function
     *
     * @return boolean
     */
    public function isRepair(): bool
    {
        return preg_match(self::CONTENT_REPAIR_STATUS_REGEX, $this->work_address);
    }

    /**
     * Обновленный метод сохранения данных в БД, дополнитльно работает
     * с комментариями, принадлежащих текущего контенту
     *
     * @param $validate - параметр для родительского метода
     * @return mixed
     */
    public function save($validate = true)
    {
        $result = parent::save($validate);
        if (empty($this->sort)) {
            $this->sort = $this->id;
            $result = parent::save($validate);
        }
        $this->correctOldComment();
        return $result;
    }
    
    /**
     * Сохраняет данные, как и метод save, но дополнительно после сохранения
     * находит контент с таким значением спецификации (specification), забирает
     * себе комментарии найденного контента, чья дата находится в пределах
     * дат начала и конца текущего контента, затем удаляет найденный контент
     * через поправленный метод delete, где используется логика смены контента
     * у комментариев
     *
     * @param $validate - параметр для родительского метода save
     * @return void
     */
    public function saveAsUnique($validate = true)
    {
        $result = $this->save($validate);
        $otherIDs = $this->getOtherIDsWithSameSpecification();
        $this->addCommentsFromIDs($otherIDs);
        if (empty($otherIDs)) return;

        foreach (static::all(['conditions' => ['id' => $otherIDs]]) as $content) {
            $content->delete();
        }
        return $result;
    }

    /**
     * Обновленный метод удаления данных в БД, дополнитльно работает
     * с комментариями, принадлежащих текущего контенту
     * 
     * @return mixed
     */
    public function delete()
    {
        $this->correctOldComment(true, false);
        return parent::delete();
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
    protected function getThrownCommentsWithNewRoles(bool $throwAll = false): array
    {
        $contentCommentIds = [];
        $zeroCommentIds = [];
        foreach ($this->getThrownTechnicComments($throwAll) as $technicId => $comments) {
            $technic = Technic::find_by_id($technicId);
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
    protected function getThrownTechnicComments(bool $throwAll = false): array
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
     * Возвращает идентификаторы контента, чье значение спецификации (specification)
     * такое же, как у текущего контента
     *
     * @return array
     */
    public function getOtherIDsWithSameSpecification(): array
    {
        if (!$this->id) return [];

        return array_map(
            function($content) { return $content->id; },
            static::all([
                'conditions' => [
                    '(specification_id = ?) AND (id <> ?)',
                    $this->specification_id,
                    $this->id
                ]
            ])
        );
    }

    /**
     * Для текущего контента забираются комментарии, принадлежащих контенту с
     * указанными идентификаторами и чья дата находится в пределах дат начала
     * и окончания текущего контента
     *
     * @param array $IDs - список идентификаторов контента
     * @return void
     */
    public function addCommentsFromIDs(array $IDs)
    {
        if (!$this->id || empty($IDs)) return;

        Comment::update_all([
                'set' => ['content_id' => $this->id],
                'conditions' => [
                    '(content_id <> ?) AND (content_id IN (?)) AND (content_date >= ?) AND (content_date <= ?)',
                    $this->id,
                    $IDs,
                    $this->begin_date->format(Day::FORMAT),
                    $this->finish_date->format(Day::FORMAT)
                ]
            ]);
    }

    /**
     * Удаление данных за конкретный день о контенте, включая и прикрепленные в
     * этот день к нему комментарии.
     * Если указанная дата находится между датами начала и конца контента, то
     * будет создан новый контент, чьи даты начала и конца будут от СЛЕДУЮЩЕГО
     * ДНЯ до ДНЯ ОКОНЧАНИЯ текущего контента, а дата ОКОНЧАНИЯ текущего контента
     * станет датой, ИДУЩЕЙ до указанной.
     * Если указанная дата совпадает с датой начала или конца, то в случае, если
     * обе даты не равны, то та дата, что совпала, будет поправлена на значение
     * близкое к значению другой даты.
     * Если указанная дата совпадает с датой начала или конца, то в случае, если
     * обе даты равны, то контент будет удален
     *
     * @param \DateTime $date - дата конкретного дня
     * @return self
     */
    public function cleanDataAtDay(\DateTime $date): self
    {
        $this->deleteCommentsAtDay($date);        
        if ($this->begin_date->format(Day::FORMAT) == $date->format(Day::FORMAT)) {
            if ($this->begin_date->format(Day::FORMAT) != $this->finish_date->format(Day::FORMAT)) {
                $this->begin_date = $date->getTimestamp() + DAY_SECOND_COUNT;
    
            } else {
                parent::delete();
                return $this;
            }
        
        } elseif (
            ($this->finish_date->format(Day::FORMAT) == $date->format(Day::FORMAT))
            || $this->createCopyViaSplitingByDate($date)
        ) {
            $this->finish_date = $date->getTimestamp() - DAY_SECOND_COUNT;

        } else {
            return $this;
        }

        parent::save(true);
        return $this;
    }

    /**
     * Удаление комментариев за конкретный день у текущего контента
     *
     * @param \DateTime $date - дата конкретного дня
     * @return self
     */
    public function deleteCommentsAtDay(\DateTime $date): self
    {
        $commentIDs = array_map(
            function($comment) { return $comment->id; },
            Comment::all(['conditions' => ['content_id' => $this->id, 'content_date' => $date]])
        );
        if (!empty($commentIDs)) Comment::delete_all(['conditions' => ['id' => $commentIDs]]);
        return $this;
    }

    /**
     * Если переданная дата находится между датами начала и конца текущего
     * контента, но не равна ни одной из них, то будет СОЗДАН объект с данными
     * текущего контента с тем же значением сортировки, но датой начала, идущей
     * после указанной даты, и датой окончания, как у текущего контента. Так же
     * новому контенту будут переданы все комментарии текущего контента в пределах
     * от даты начала до даты окончания нового контента
     *
     * @param \DateTime $date - дата конкретного дня
     * @return Content|boolean
     */
    public function createCopyViaSplitingByDate(\DateTime $date): ?self
    {
        $content = $this->prepareCopyAfterDay($date);
        if (!$content) return null;

        $content->save();
        $this->setHostForComments($content);
        return $content;
    }

    /**
     * Если переданная дата находится между датами начала и конца текущего
     * контента, но не равна ни одной из них, то будет ПОДГОТОВЛЕН (без
     * сохранения в БД) объект с данными текущего контента с тем же значением
     * сортировки, но датой начала, идущей после указанной даты, и датой
     * окончания, как у текущего контента
     *
     * @param \DateTime $date - дата конкретного дня
     * @return Content|boolean
     */
    public function prepareCopyAfterDay(\DateTime $date): ?self
    {
        $currentDay = $date->format(Day::FORMAT);
        if (
            ($currentDay <= $this->begin_date->format(Day::FORMAT))
            || ($currentDay >= $this->finish_date->format(Day::FORMAT))
        ) return null;

        $content = $this->getPreparedCopyWithoutFields(['begin_date', 'finish_date', 'sort']);
        $content->sort = $this->sort;
        $content->begin_date = $date->getTimestamp() + DAY_SECOND_COUNT;
        $content->finish_date = $this->finish_date->getTimestamp();
        return $content;
    }

    /**
     * Для указанного контента забираются комментарии текущего контента,
     * чье даты находятся в пределах дат начала и конца указанного контента
     *
     * @param Content $content - какой-то контент
     * @return self
     */
    public function setHostForComments(Content $content): self
    {
        if (!$content->id || !$this->id) return $this;

        Comment::update_all([
            'set' => [
                'content_id' => $content->id
            ],
            'conditions' => [
                '(content_id = ?) AND (content_date >= ?) AND (content_date <= ?)',
                $this->id,
                $content->begin_date->format(Day::FORMAT),
                $content->finish_date->format(Day::FORMAT),
            ]
        ]);
        return $this;
    }
};