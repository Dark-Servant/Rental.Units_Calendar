<?
namespace Frontend\MainTable\CellData;

/**
 * Undocumented class
 */
class ChosenTechnic
{
    protected $modelUnit = false;

    /**
     * Undocumented function
     *
     * @param \ChosenTechnic $modelUnit
     */
    public function __construct(\ChosenTechnic $modelUnit)
    {
        $this->modelUnit = $modelUnit;
    }

    /**
     * Undocumented function
     *
     * @param array& $data
     * @return self
     */
    public function setChoiceToList(array&$data): self
    {
        if (!empty($data[$this->modelUnit->entity_id]))
            $data[$this->modelUnit->entity_id]['IS_CHOSEN'] = true;

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function isPartner(): bool
    {
        return $this->modelUnit->is_partner;
    }
}