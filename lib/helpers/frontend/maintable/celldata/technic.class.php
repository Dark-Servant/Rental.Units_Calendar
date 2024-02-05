<?
namespace Frontend\MainTable\CellData;

/**
 * Undocumented class
 */
class Technic
{
    const PARTNER_CONTENT_DAY_SHOWING = 'P';
    const TECHNIC_CONTENT_DAY_SHOWING = 'T';

    protected $modelUnit;
    protected $dateStamps = [];
    protected $lastListData = [];
    protected $contentDayShowing = [];
    protected $contentDayShowingCode = false;

    /**
     * Undocumented function
     *
     * @param \Technic $modelUnit
     */
    public function __construct(\Technic $modelUnit)
    {
        $this->modelUnit = $modelUnit;
    }

    /**
     * Undocumented function
     *
     * @param array $dateStamps
     * @return self
     */
    public function setDateStampInterval(array $dateStamps): self
    {
        $this->dateStamps = $dateStamps;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param array& $contentDayShowing
     * @return self
     */
    public function setContentDayShowing(array&$contentDayShowing): self
    {
        $this->contentDayShowing = &$contentDayShowing;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function isPartnerData(): bool
    {
        return !$this->modelUnit->is_my && $this->modelUnit->partner_id;
    }

    /**
     * Undocumented function
     *
     * @param array& $data
     * @return self
     */
    public function addAsParnerAtList(array&$data): self
    {
        if (isset($data[$this->modelUnit->partner_id])) return $this;

        $this->lastListData = [
            'ID' => $this->modelUnit->partner_id,
            'EXTERNAL_ID' => $this->modelUnit->external_id,
            'IS_PARTNER' => true,
            'IS_CHOSEN' => false,
            'CONTENTS' => array_fill_keys($this->dateStamps, false)
        ];
        $this->contentDayShowingCode = self::PARTNER_CONTENT_DAY_SHOWING . $this->lastListData['ID'];
        $data[$this->modelUnit->partner_id] = &$this->lastListData;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param array& $data
     * @return self
     */
    public function addAtList(array&$data): self
    {
        if (isset($data[$this->modelUnit->id])) return $this;

        $this->lastListData = [
            'ID' => $this->modelUnit->id,
            'NAME' => $this->modelUnit->name,
            'EXTERNAL_ID' => $this->modelUnit->external_id,
            'IS_PARTNER' => false,
            'STATE_NUMBER' => $this->modelUnit->state_number,
            'IS_CHOSEN' => false,
            'CONTENTS' => array_fill_keys($this->dateStamps, false)
        ];
        $this->contentDayShowingCode = self::TECHNIC_CONTENT_DAY_SHOWING . $this->lastListData['ID'];
        $data[$this->modelUnit->id] = &$this->lastListData;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param \Content $content
     * @return self
     */
    public function addContentAtInterval(\Content $content): self
    {
        if (empty($this->lastListData)) return $this;

        (new Content($content))->fillDayTimeStampWithShowingSetting($this->lastListData['CONTENTS'], $this->contentDayShowing[$this->contentDayShowingCode]);
        return $this;
    }
}