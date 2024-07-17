<?
namespace Frontend\MainTable\CellData;

/**
 * Undocumented class
 */
class Comment
{
    const TECHNIC_RESULT_DATA_CODE = 'COMMENTS';

    protected $modelUnit = false;
    protected $data = [];

    /**
     * Undocumented function
     *
     * @param \Comment $modelUnit
     */
    public function __construct(\Comment $modelUnit)
    {
        $this->modelUnit = $modelUnit;
    }

    /**
     * Undocumented function
     *
     * @param array& $data
     * @return self
     */
    public function addDataToTechnic(array&$data): self
    {
        $dayTimestamp = $this->modelUnit->content_date->getTimestamp();

        $data[self::TECHNIC_RESULT_DATA_CODE][$dayTimestamp][] = &$this->getCellData();
        if ($this->modelUnit->duty_status)
            $data[Content::TECHNIC_RESULT_DATA_CODE][$dayTimestamp]['STATUS_CLASS'] = \Comment::DUTY_STATUS[$this->modelUnit->duty_status];
        return $this;
    }

    /**
     * Возвращает данные комментария, которые используются при выводе
     * в календаре
     * 
     * @param bool $isRead - отметка, что комментарий прочитан
     * @return array
     */
    public function&getCellData(bool $isRead = false): array
    {
        if (empty($this->$data))
            $this->$data = [
                'ID' => $this->modelUnit->id,
                'TECHNIC_ID' => $this->modelUnit->technic_id,
                'CONTENT_ID' => $this->modelUnit->content_id,
                'USER_ID' => $this->modelUnit->user->external_id,
                'USER_NAME' => $this->modelUnit->user->name,
                'VALUE' => $this->modelUnit->value,
                'DUTY_STATUS_NAME' => $this->modelUnit->duty_status
                                    ? \Comment::DUTY_STATUS[$this->modelUnit->duty_status]
                                    : '',
                'CREATED_AT' => $this->modelUnit->created_at->getTimestamp(),
            ];

        static::setReadStatusByValueAtData($isRead, $this->$data);
        return $this->$data;
    }

    /**
     * Undocumented function
     *
     * @param \ReadCommentMark $mark
     * @param array& $data
     * @return void
     */
    public static function setReadStatusByMarkAtList(\ReadCommentMark $mark, array&$data)
    {
        if (!empty($data[$mark->comment_id]))
            static::setReadStatusByValueAtData(true, $data[$mark->comment_id]);
    }

    /**
     * Undocumented function
     *
     * @param boolean $isRead
     * @param array& $data
     * @return void
     */
    public static function setReadStatusByValueAtData(bool $isRead, array&$data)
    {
        $data['READ'] = $isRead;
    }
}