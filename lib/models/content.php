<?

class Content extends InfoserviceModel
{
    static $belongs_to = [
        ['technic'],
        ['responsible'],
        ['customer'],
    ];

    /**
     * 
     */
    const CONTENT_DEAL_STATUS = [
        'waiting', 'process', 'final', 'closed', 'many'
    ];

    /**
     * 
     */
    const CONTENT_DEAL_STATUS_REGEX = [
        '/\b(?:нов(?:ая|ый|ое|ые)|резерв) +/iu',
        '/\bпроведение +$/iu',
        '/\b(?:финал(?:ьн(?:ая|ый|ое|ые))?|закрыт(?:ая|ый|ое|ы)?)\b/iu'
    ];

    /**
     * [correctStatusValue description]
     * 
     * @param  [type] $name   [description]
     * @param  [type] &$value [description]
     * @return boolean
     */
    protected function correctStatusValue($name, &$value)
    {
        if (strtolower($name) != 'status') return false;

        $regexValues = array_values(self::CONTENT_DEAL_STATUS_REGEX);
        $newValue = null;
        foreach ($regexValues as $newValueNumber => $statusRegex) {
            if (preg_match($statusRegex, $value)) {
                $newValue = $newValueNumber;
                break;
            }
        }
        $value = isset($newValue) ? $newValue : count($regexValues);
        return true;
    }
};