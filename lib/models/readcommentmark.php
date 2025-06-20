<?

class ReadCommentMark extends Models\InfoserviceBase
{
    static $belongs_to = [
        ['user', 'foreign_key' => 'user_id', 'class_name' => 'Responsible'],
        ['comment'],
    ];

    private $toCheck = true;

    /**
     * Обновленный метод сохранения данных в БД
     *
     * @param $validate - параметр для родительского метода
     * @return mixed
     */
    public function save($validate = true)
    {
        if ($this->id || !$this->comment_id || !$this->user_id
            || (
                $this->toCheck
                && static::first([
                       'comment_id' => $this->comment_id,
                       'user_id' => $this->user_id
                    ])
            )) return;

        return parent::save($validate);
    }

    /**
     * Устанавливает для пользователя отметки, что им были прочитаны
     * конкретные комментарии
     * 
     * @param int $userId - идентификатор пользователя
     * @param $commentIds - идентификатор комментария или список
     * идентификаторов комментариев
     * 
     * @return void
     */
    public static function setMark(int $userId, $commentIds)
    {
        if (!is_array($commentIds)) $commentIds = [$commentIds];

        $readIds = array_map(
                        function($comment) { return $comment->comment_id; },
                        static::all([
                            'user_id' => $userId,
                            'comment_id' => $commentIds
                        ])
                    );
        foreach (
            array_filter(
                $commentIds,
                function($id) use($readIds) { return !in_array($id, $readIds); }
            ) as $commentId
        ) {
            if (!($id = intval($commentId)) || ($id < 1)) continue;

            $comment = new static();
            $comment->toCheck = false;
            $comment->set_attributes(['comment_id' => $id, 'user_id' => $userId]);
            $comment->save();
        }
    }
};