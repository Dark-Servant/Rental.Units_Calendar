<?
namespace Frontend\MainTable\CellData;

/**
 * Undocumented class
 */
class Content
{
    const TECHNIC_RESULT_DATA_CODE = 'CONTENTS';

    const CUSTOMER_PREFIX_UNIQUE_CODE = 'n:';
    const URL_PREFIX_UNIQUE_CODE = 'u:';

    protected $modelUnit;
    protected $data = [];

    public function __construct(\Content $modelUnit)
    {
        $this->modelUnit = $modelUnit;
    }

    /**
     * Undocumented function
     *
     * @param array& $dayTimeStamps
     * @param array&|null $dayShowing
     * @return self
     */
    public function fillDayTimeStampWithShowingSetting(array&$dayTimeStamps, array&$dayShowing=null): self
    {
        $cellData = $this->getCellData();
        $uniqueCode = static::getUniqueCodeByCellData($cellData);
        $contentStatus = $this->getTrueStatus();
        foreach ($this->contentIntervalDays() as $timeStamp) {
            if (!isset($dayTimeStamps[$timeStamp])) continue;

            /**
             * Проверка на то, был ли конкретный контент выведен в текущей ячейке. Если да, то он уже отметился
             * для текущей техники (партнера) и даты, поэтому устанавливается, что его не надо выводить.
             */
            $cellData['CELL_SHOWING'] = empty($dayShowing[$timeStamp][$uniqueCode]);
            $dayShowing[$timeStamp][$uniqueCode] = true;

            /**
             * Из-за бага с дублированнием контента, когда действие БП запускается параллельно несколько раз
             * при, возможно, нескольких раз обращений из шаблона БП, приходится делать проверку статуса контента
             * только для того контента, который еще не отметился для текущей даты и техники
             */
            if ($cellData['CELL_SHOWING']) 
                $this->setDayStatusAtCellByContentStatus($dayTimeStamps[$timeStamp], $contentStatus);

            if (!isset($dayTimeStamps[$timeStamp]['DEALS']))
                $dayTimeStamps[$timeStamp]['DEALS'] = [];

            // необходимо, чтобы контент на ремонте был всегда в начале списка
            if ($cellData['IS_REPAIR']) {
                array_unshift($dayTimeStamps[$timeStamp]['DEALS'], $cellData);

            } else {
                $dayTimeStamps[$timeStamp]['DEALS'][] = $cellData;
            }
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function contentIntervalDays(): array
    {
        return range(
                    $this->modelUnit->begin_date->getTimestamp(),
                    $this->modelUnit->finish_date->getTimestamp(),
                    \Day::SECOND_COUNT
                );
    }

    /**
     * Возвращает массив с данными контента, которые надо использовать
     * при выводе в календаре
     * 
     * @return array
     */
    public function&getCellData(): array
    {
        global $langValues;
        if (!empty($this->data)) return $this->data;

        $dealURL = $this->modelUnit->deal_url;
        forward_static_call_array([get_class($this->modelUnit), 'correctURLValue'], ['deal_url', &$dealURL]);

        $this->data = [
            'ID' => $this->modelUnit->id,
            'DEAL_URL' => $dealURL,
            'RESPONSIBLE_NAME' => $this->modelUnit->responsible->name,
            'CUSTOMER_NAME' => $this->modelUnit->customer->name,
            'WORK_ADDRESS' => $this->modelUnit->work_address
        ];
        if (!$this->modelUnit->technic->is_my)
            $this->data += [
                'TECHNIC_ID' => $this->modelUnit->technic_id,
                'TECHNIC_NAME' => $this->modelUnit->technic->name
            ];

        if ($this->modelUnit->isRepair()) {
            $this->data['DEAL_URL'] = 
            $this->data['RESPONSIBLE_NAME'] =
            $this->data['WORK_ADDRESS'] = '';
            $this->data['CUSTOMER_NAME'] = $langValues['CONTENT_REPAIR_STATUS_TITLE'];
            $this->data['IS_REPAIR'] = true;
        }

        return $this->data;
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @return string
     */
    protected static function getUniqueCodeByCellData(array $data): string
    {
        /**
         * Устанавливаем параметр $dealName, чтобы потом проверить не выводился ли этот контент
         * в той же ячейке
         */
        if (empty($data['DEAL_URL']) || !preg_match('/\/(\d+)/', $data['DEAL_URL'], $URLParts)) {
            return self::CUSTOMER_PREFIX_UNIQUE_CODE . trim(strtolower($data['CUSTOMER_NAME']));

        } else {
            return self::URL_PREFIX_UNIQUE_CODE . $URLParts[1];
        }
    }

    /**
     * Undocumented function
     *
     * @return integer
     */
    public function getTrueStatus(): int
    {
        if ($this->modelUnit->isRepair()) {
            return CONTENT_REPAIR_DEAL_STATUS;

        } elseif ($this->modelUnit->is_closed) {
            return CONTENT_CLOSED_DEAL_STATUS;

        } else {
            return $this->modelUnit->status >= CONTENT_MAX_DEAL_STATUS
                 ? CONTENT_MAX_DEAL_STATUS
                 : $this->modelUnit->status;
        }
    }

    /**
     * Undocumented function
     *
     * @param array $dayCell
     * @return void
     */
    protected static function setDayStatusAtCellByContentStatus(&$dayCell, int $statusValue)
    {
        if (
            !isset($dayCell['STATUS'])
            || ($statusValue == CONTENT_REPAIR_DEAL_STATUS)
        ) {
            $dayCell['STATUS'] = $statusValue;
            $dayCell['STATUS_CLASS'] = \Content::CONTENT_DEAL_STATUS[$statusValue];
            
        } elseif (
            ($dayCell['STATUS'] != CONTENT_REPAIR_DEAL_STATUS)
            && ($dayCell['STATUS'] != $statusValue)
        ) {
            $dayCell['STATUS'] = CONTENT_MANY_DEAL_STATUS;
            $dayCell['STATUS_CLASS'] = \Content::CONTENT_DEAL_STATUS[CONTENT_MANY_DEAL_STATUS];
        }

        ++$dayCell['DEAL_COUNT'];
        $dayCell['IS_ONE'] = $dayCell['DEAL_COUNT'] == \Content::MIN_DEAL_COUNT;
        $dayCell['VERY_MANY'] = $dayCell['DEAL_COUNT'] > \Content::MAX_DEAL_COUNT;
    }
}