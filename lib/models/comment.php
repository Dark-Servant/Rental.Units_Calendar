<?

class Comment extends InfoserviceModel
{
    static $belongs_to = [
        ['user', 'foreign_key' => 'user_id', 'class_name' => 'Responsible'],
        ['technic'],
        ['content'],
    ];

    /**
     * Возвращает данные комментария, которые используются при выводе
     * в календаре
     * 
     * @return array
     */
    function getData()
    {
        return [
            'ID' => $this->id,
            'TECHNIC_ID' => $this->technic_id,
            'CONTENT_ID' => $this->content_id,
            'USER_ID' => $this->user->external_id,
            'USER_NAME' => $this->user->name,
            'VALUE' => $this->value,
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
        if (!$this->id) return;

        $this->technic_id = $this->oldParamData['technic_id']['value'];
        $this->content_date = $this->oldParamData['content_date']['value'];
    }

    /**
     * Обновленный метод сохранения данных в БД
     * 
     * @return mixed
     */
    public function save()
    {
        $this->correctImortantFields();
        return parent::save();
    }
};