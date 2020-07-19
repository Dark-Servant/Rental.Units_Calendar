<?

class InfoserviceModel extends ActiveRecord\Model
{
    public static function getWithAddedConditions(array $main, array $conditions)
    {
        $mainConditions = '';
        if (empty($main)) {
            $main[] = &$mainConditions;
        
        } else {
            $mainConditions = &$main[0];
        }

        $addConditions = $conditions;
        $firstKey = current(array_keys($conditions));
        if (($firstKey !== false) && !is_string($firstKey) && is_string($conditions[$firstKey])) {
            $mainConditions .= (empty($mainConditions) ? '' : ' AND ') . $conditions[$firstKey];
            $addConditions = array_slice($conditions, 1);
        }

        foreach ($addConditions as $field => $value) {
            $mainConditions .= (empty($mainConditions) ? '' : ' AND ');
            if (is_string($field)) {
                $mainConditions .= '(' . $field . ' = ?)';

            } else {
                $mainConditions .= '(?)';
            }

            $main[] = $value;
        }
        return $main;
    }

    /**
     * [correctBooleanValue description]
     * 
     * @param  [type] $name   [description]
     * @param  [type] &$value [description]
     * @return boolean
     */
    protected function correctBooleanValue($name, &$value)
    {
        if (preg_match('/^is_/i', $name)) {
            if (is_string($value)) $value = intval(preg_match('/^y(?:es)?$/i', $value));
            return true;
        }
        return false;
    }

    /**
     * [correctDateValue description]
     * 
     * @param  [type] $name   [description]
     * @param  [type] &$value [description]
     * @return boolean
     */
    protected function correctDateValue($name, &$value)
    {
        if (!preg_match('/_date$/i', $name) || !is_string($value)) return false;

        $newValue = date_create_from_format(DAY_CALENDAR_FORMAT, $value);
        if (!$newValue) $newValue = date_create_from_format(DAY_FORMAT, $value);

        if (isset($newValue)) {
            $value = $newValue;
            return true;
        }
        return false;
    }

    /**
     * [__set description]
     * 
     * @param [type] $name  [description]
     * @param [type] $value [description]
     * @return void
     */
    public function __set($name, $value)
    {
        foreach (get_class_methods($this) as $method) {
            if (preg_match('/^correct\w+value$/i', $method)) {
                if ($this->$method($name, $value)) break;
            }
        }
        parent::__set($name, $value);
    }
};