<?

class InfoserviceModel extends ActiveRecord\Model
{
    protected static $correctionMethods = null;

    /**
     * Объединяет фильтр, описанный как
     *     "<строка с условиями>", <значение параметра 1>, <значение параметра 2>, ..., <значение параметра N>
     * где
     *     <строка с условиями> - общее описание условия с OR, AND, вложенными условиями. Может уже содержать
     *     готовое условие с подставленными значениями или быть со знаками вопроса, на место которых потом будут
     *     подставлены по порядку значения каждого парамера, указанных после этого условия
     * с фильтром из $conditions, который может быть описан так же как и $main или иметь описание вроде
     *     <поле 1> => <значение 1>, ...
     * или иметь и то и другое вперемешку. Результат будет возвращен как готовый фильтр в том виде, в которым
     * должен быть описан фильтр из $main
     * 
     * @param array $main - основная часть фильтра, не подтвергается изменению,
     * ее содержимое дополненяется условиями из $conditions
     * 
     * @param array $conditions - дополнительные условия для фильтра
     * @return array
     */
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
                $mainConditions .= '(' . $field . (is_array($value) ? ' IN (?)' : ' = ?') . ')';

            } elseif (!is_array($value)) {
                $mainConditions .= '(?)';

            } else {
                continue;
            }

            $main[] = $value;
        }
        return $main;
    }

    /**
     * Проверяет название поля. Если оно начинается с is_*, то значение будет приведено
     * к числовому. Для числовых значений ничего не изменится, а строковые со значением
     * 'y', 'yes' или 'true' будут приведены к 1, остальные значения будут приниматься
     * как нуль.
     * 
     * @param $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    protected function correctBooleanValue($name, &$value)
    {
        if (preg_match('/^is_/i', $name)) {
            $value = intval(is_string($value) ? preg_match('/^(?:y(?:es)?|true)$/i', $value) : $value);
            return true;
        }
        return false;
    }

    /**
     * Проверяет название поля. Если оно оканчивается на *_url, то значение
     * должно начинаться на http(s), иначе значение становится пустым
     *
     * @param $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    protected function correctURLValue($name, &$value)
    {
        if (preg_match('/_url$/i', $name)) {
            $value = preg_match('/^https?:\/\//', $value) ? $value : '';
            return true;
        }
        return false;
    }

    /**
     * Проверяет название поля. Если оно оканчивается на *_date, то значение заменяется
     * экземпляром класса DateTime. Само значение для этого должно быть строкового типа
     * и иметь значние даты в формате, описанном в константах Day::CALENDAR_FORMAT или
     * Day::FORMAT
     * 
     * @param $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    protected function correctDateValue($name, &$value)
    {
        if (!preg_match('/_date$/i', $name) || !is_string($value)) return false;

        $newValue = date_create_from_format(Day::CALENDAR_FORMAT, $value);
        if (!$newValue) $newValue = date_create_from_format(Day::FORMAT, $value);

        if (isset($newValue)) {
            $value = $newValue;
            return true;
        }
        return false;
    }

    /**
     * Для класса, в экземпляре которого была вызвана эта функция, метод сначала собирает и
     * сохраняет в статической переменной все методы для проверки значений по каждому полю,
     * если еще не делал это для этого класса. При следующих вызовах для того же класса поиск
     * и сохранение подходящих методов больше не будет происходить. Далее метод возвращает по
     * одному названия собранных методов для класса, из которого его вызвали. Возвращаемые названия
     * методов используются для проверки значения каждого поля экземпляра класса
     * 
     * @yield
     */
    protected function correctionMethods()
    {
        $className = get_called_class();
        if (empty(self::$correctionMethods[$className])) {
            self::$correctionMethods[$className] = [];
            foreach (get_class_methods($this) as $method) {
                if (!preg_match('/^correct\w+value$/i', $method)) continue;

                self::$correctionMethods[$className][] = $method;
            }
        }
        foreach (self::$correctionMethods[$className] as $method) {
            yield $method;
        }
    }

    /**
     * Для случаев вроде присваивания
     *     <экземпляр>-><поле> = <значение>;
     * прогоняет по каждому методу класса, имеющего название по шаблону
     *     correct<Допольнительный текст>Value
     * до тех пор, пока кто-то из таких методов не вернет true или все не
     * получат нзвание и значение поля
     * 
     * @param $name - название поля
     * @param &$value - значение поля
     * @return void
     */
    public function __set($name, $value)
    {
        foreach ($this->correctionMethods() as $method) {
            if ($this->$method($name, $value)) break;
        }
        parent::__set($name, $value);
    }

    /**
     * Возвращает правильное значение для нужного поля экземпляра класса
     * 
     * @param $name - название поля
     * @return mixed
     */
    public function __get($name)
    {
        $value = parent::__get($name);
        foreach ($this->correctionMethods() as $method) {
            if ($this->$method($name, $value)) break;
        }
        return $value; 
    }
};