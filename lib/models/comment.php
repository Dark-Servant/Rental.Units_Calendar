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
            'CREATED_AT' => $this->created_at->getTimestamp(),
        ];
    }

    /**
     * Обновленный метод сохранения данных в БД
     * 
     * @return mixed
     */
    public function save()
    {
        $isNew = !$this->id;
        $result = parent::save();
        if ($isNew) ReadCommentMark::setMark($this->user_id, $this->id);
        return $result;
    }
};