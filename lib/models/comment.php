<?

class Comment extends InfoserviceModel
{
    static $belongs_to = [
        ['user', 'foreign_key' => 'user_id', 'class_name' => 'Responsible'],
        ['technic'],
        ['content'],
    ];

    static $has_many = [
        ['readmarks', 'class_name' => 'ReadCommentMark']
    ];

    const NOT_CHANGED_FIELDS = ['technic_id', 'content_date', 'user_id'];
    const CHILD_NAMES_FOR_DELETING = ['readmarks'];

    // Классы дежурных комментариев
    const DUTY_STATUS = [1 => 'repair', 'on-road', 'based-on'];

    /**
     * Возвращает данные комментария, которые используются при выводе
     * в календаре
     * 
     * @param bool $isRead - отметка, что комментарий прочитан
     * @return array
     */
    function getData(bool $isRead = false)
    {
        return [
            'ID' => $this->id,
            'TECHNIC_ID' => $this->technic_id,
            'CONTENT_ID' => $this->content_id,
            'USER_ID' => $this->user->external_id,
            'USER_NAME' => $this->user->name,
            'VALUE' => $this->value,
            'READ' => $isRead,
            'DUTY_STATUS_NAME' => $this->duty_status
                                ? self::DUTY_STATUS[$this->duty_status]
                                : '',
            'CREATED_AT' => $this->created_at->getTimestamp(),
        ];
    }

    /**
     * Проверяет правильность значения поля content_id, это поле должно быть
     * равно нулю, иначе проверкой поля займется метод correctIDValue
     * 
     * @param string $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    protected function correctContentIdValue(string $name, &$value)
    {
        return ($name === 'content_id') && ($value === 0);
    }

    /**
     * Обработчик проверки наличия правильности указания связи с контентом.
     * Если указан контент не с той же датой или техникой, как у комментария,
     * то для комментария выбирает тот контент, который соответствует указанной
     * у него даты и техники или техник партнера, если техника не "своя"
     * 
     * @return boolean
     */
    protected function checkContentLink()
    {
        if (!$this->technic || empty($this->content_date)) return false;

        $content = $this->content;
        if (
            $content && ($content->technic_id === $this->technic_id)
            && ($content->begin_date <= $this->content_date)
            && ($content->finish_date >= $this->content_date)
        ) return true;

        $partner = $this->technic->partner;
        $technicIds = $partner
                    ? array_map(
                            function($technic) { return $technic->id; },
                            $partner->technics
                        )
                    : [$this->technic_id];
        $contentDate = $this->content_date->format(Day::FORMAT);
        $content = Content::first([
                        'conditions' => [
                            '(technic_id IN (?)) AND (begin_date <= ?) AND (finish_date >= ?)',
                            $technicIds, $contentDate, $contentDate,
                        ],
                        'order' => 'id ASC'
                    ]);

        if (empty($content)) {
            $this->content_id = 0;

        } else {
            $this->content_id = $content->id;
            $this->technic_id = $content->technic_id;
        }
        return true;
    }

    /**
     * Обновленный метод сохранения данных в БД
     *
     * @param $validate - параметр для родительского метода
     * @return mixed
     */
    public function save($validate = true)
    {
        if (!$this->checkContentLink()) return;
        $isNew = !$this->id;
        $result = parent::save($validate);
        if ($isNew && $this->id) ReadCommentMark::setMark($this->user_id, $this->id);
        return $result;
    }
};