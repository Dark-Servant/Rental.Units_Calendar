<?
namespace Frontend\MainTable\RowData;

use Frontend\MainTable\CellData\{
    Technic as CellTechnic,
    Comment as CellComment,
    ChosenTechnic as CellChosenTechnic,
};

/**
 * Undocumented class
 */
class TechnicInterval
{
    protected $dateStamps = [];
    protected $startDayTimestamp = [];
    protected $finishDayTimestamp = [];
    
    protected $contentDayShowing = [];
    protected $technicPartners = [];
    protected $technics = [];
    protected $partners = [];

    protected $comments = [];

    protected $lastTechnicUnit = false;

    /**
     * Undocumented function
     *
     * @param array $dateStamps
     */
    public function __construct(array $dateStamps)
    {
        $this->dateStamps = $dateStamps;
        $this->startDayTimestamp = reset($dateStamps);
        $this->finishDayTimestamp = end($dateStamps);
    }

    /**
     * Undocumented function
     *
     * @param \Technic $modelUnit
     * @return self
     */
    public function addTechnic(\Technic $modelUnit): self
    {
        $this->lastTechnicUnit = (new CellTechnic($modelUnit))->setDateStampInterval($this->dateStamps)->setContentDayShowing($this->contentDayShowing);
        if ($this->lastTechnicUnit->isPartnerData()) {
            $this->lastTechnicUnit->addAsParnerAtList($this->partners);
            $this->technicPartners[$modelUnit->id] = $modelUnit->partner_id;

        } else {
            $this->lastTechnicUnit->addAtList($this->technics);
            $this->technicPartners[$modelUnit->id] = false;
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param \Content $modelUnit
     * @return self
     */
    public function addToLastTechnicNextContent(\Content $modelUnit): self
    {
        if (!($this->lastTechnicUnit instanceof CellTechnic)) return $this;

        $this->lastTechnicUnit->addContentAtInterval($modelUnit);
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return self
     */
    public function loadComments(): self
    {
        $filter = [
            '(technic_id IN (?)) AND (content_date >= ?) AND (content_date <= ?)',
            $this->getAllTechnicIDs() ?: null,
            (new \DateTime)->setTimestamp($this->startDayTimestamp),
            (new \DateTime)->setTimestamp($this->finishDayTimestamp),
        ];

        foreach (\Comment::all(['conditions' => $filter, 'order' => 'id asc']) as $comment) {
            $this->addComment($comment);
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param \Comment $comment
     * @return self
     */
    public function addComment(\Comment $comment): self
    {
        $partnerID = $this->technicPartners[$comment->technic_id];
        if ($partnerID) {
            $technicRow = &$this->partners[$partnerID];

        } elseif (!empty($this->technics[$comment->technic_id])) {
            $technicRow = &$this->technics[$comment->technic_id];

        } else {
            return $this;
        }
        $this->comments[$comment->id] = &(new CellComment($comment))->addDataToTechnic($technicRow)->getCellData();
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param integer $userID
     * @return self
     */
    public function prepareReadyDataForUserID(int $userID): self
    {
        return $this->loadChoicesForUserID($userID)->setCommentReadMarkForUserID($userID);
    }

    /**
     * Undocumented function
     *
     * @param integer $userID
     * @return self
     */
    public function loadChoicesForUserID(int $userID): self
    {
        if ($userID < 1) return $this;

        $values = [];
        $conditions = '';
        foreach ([$this->getSimpleTechnicIDs(), $this->getPartnerIDs()] as $number => $IDs) {
            if (empty($IDs)) continue;

            $conditions .= ($conditions ? ' OR ' : '') . '((is_partner = ' . $number . ') AND (entity_id IN (?)))';
            $values[] = $IDs;
        }
        $conditions = '(' . $conditions . ') AND (is_active = 1) AND (user_id = ?)';
        $values[] = $userID;

        foreach (
            \ChosenTechnic::all(['conditions' => array_merge([$conditions], $values)]) as $choice
        ) {
            $this->addChoice($choice);
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param \ChosenTechnic $choice
     * @return void
     */
    public function addChoice(\ChosenTechnic $choice): self
    {
        $choice = new CellChosenTechnic($choice);
        if ($choice->isPartner()) {
            $choice->setChoiceToList($this->partners);

        } else {
            $choice->setChoiceToList($this->technics);
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param integer $userID
     * @return self
     */
    public function setCommentReadMarkForUserID(int $userID): self
    {
        if (!count($this->comments) || ($userID < 1)) return $this;

        foreach (
            \ReadCommentMark::all([
                'user_id' => $userID,
                'comment_id' => array_keys($this->comments)
            ]) as $mark
        ) {
            CellComment::setReadStatusByMarkAtList($mark, $this->comments);
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getResult(): array
    {
        $technicResult = array_values($this->technics);
        if (!empty($this->partners)) {
            foreach (\Partner::all(['conditions' => ['id' => $this->getPartnerIDs()], 'order' => 'name ASC']) as $partner) {
                $technicResult[] = [
                    'NAME' => $partner->name,
                    'IS_PARTNER' => true
                ] + $this->partners[$partner->id];
            }
        }
        return $technicResult;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getAllTechnicIDs(): array
    {
        return array_keys($this->technicPartners);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getSimpleTechnicIDs(): array
    {
        return array_keys($this->technics);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getSimpleTechnics(): array
    {
        return $this->technics;
    }

    /**
     * Undocumented function
     *
     * @return integer
     */
    public function getSimpleTechnicCount(): int
    {
        return count($this->technics);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getPartnerIDs(): array
    {
        return array_keys($this->partners);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getPartners(): array
    {
        return $this->partners;
    }

    /**
     * Undocumented function
     *
     * @return integer
     */
    public function getPartnerCount(): int
    {
        return count($this->partners);
    }
}