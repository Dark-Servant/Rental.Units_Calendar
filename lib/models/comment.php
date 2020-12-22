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
     * Поправляет важные поля в комментариях
     * 
     * @return void
     */
    protected function correctImortantFields()
    {
        foreach (['technic_id', 'content_date'] as $fieldName) {
            if (!isset($this->oldParamData[$fieldName])) continue;

            $this->$fieldName = $this->oldParamData[$fieldName]['value'];
        }
    }

    /**
     * Обновленный метод сохранения данных в БД
     * 
     * @return mixed
     */
    public function save()
    {
        $this->correctImortantFields();
        $result = parent::save();
        ReadCommentMark::create(['comment_id' => $this->id, 'user_id' => $this->user_id]);
        return $result;
    }
};