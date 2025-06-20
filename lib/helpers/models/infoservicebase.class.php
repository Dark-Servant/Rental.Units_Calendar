<?
namespace Models;

class InfoserviceBase extends \ActiveRecord\Model
{
    protected static $correctionMethods = null;
    protected $oldParamData = [];

    /**
     * Условия для доступа к конкретным полям экземпляров моделей. Сами условия описываются
     * как массивы, где под "ключами" указываются поля экземпляра, а под "элементами" массива
     * требуемые значения для полей. Массивы с требуемыми значениями конкретных полей указываются
     * под "ключом", имя которого совпадает с именем поля, доступ к которому и надо сделать условным.
     * Например,
     *     'testField' => ['digit' => 100]
     * Доступ к полю testField будет возможен, если другое поле - digit - равно 100.
     * Это функционал нужен, чтобы для моделей, у которых в одном поле может быть записана ссылка на
     * элемент из разных моделей, можно было указать, что получить элемент конкретной модели, на которую
     * можно сослаться по значению поля, возможно при наличии конкретных значений в других полях
     */
    const FIELD_EXISTENCE_CONDITIONS = [];

    /**
     * Здесь перечисляются поля, значения которых надо восстанавливать, когда идет сохранение изменений
     * в экземпляре
     */
    const NOT_CHANGED_FIELDS = [];

    /**
     * Здесь перечисляются имена, указанные в has_many, для доступа к экземплярам других моделей,
     * чтобы при удалении экзепляра какой-то модели были так же удалены все экземпляры дргуих
     * моделей, связанные с удаляемой моделью
     */
    const CHILD_NAMES_FOR_DELETING = [];

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
     * @param array $main - основная часть фильтра, не подтвергается изменению, ее содержимое дополненяется
     * условиями из $conditions
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
     * @param string $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    public static function correctBooleanValue(string $name, &$value)
    {
        if (preg_match('/^is_/i', $name)) {
            $value = intval(is_string($value) ? preg_match('/^(?:y(?:es)?|true|\-? *[1-9]\d*)$/i', $value) : $value);
            return true;
        }
        return false;
    }

    /**
     * Проверяет название поля. Если оно оканчивается на *_url, то значение
     * должно начинаться на http(s), иначе значение становится пустым
     *
     * @param string $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    public static function correctURLValue(string $name, &$value)
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
     * и иметь значние даты в формате, описанном в константах \Day::CALENDAR_FORMAT или
     * Day::FORMAT, или быть меткой времени
     * 
     * @param string $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    public static function correctDateValue(string $name, &$value)
    {
        if (!preg_match('/_date$/i', $name)) return false;

        $newValue = null;
        if (is_string($value) && !preg_match('/^\d+$/', $value)) {
            $newValue = date_create_from_format(\Day::CALENDAR_FORMAT, $value);
            if (!$newValue) $newValue = date_create_from_format(\Day::FORMAT, $value);
        
        } elseif (is_integer($value) || is_string($value)) {
            $newValue = new \DateTime;
            $newValue->setTimestamp(intval($value));
        }

        if (isset($newValue)) {
            $value = $newValue;
            return true;
        }
        return false;
    }

    /**
     * Проверяет название поля. Если оно оканчивается на *_id, то значение должно
     * быть либо числового типа, либо строкового, но так же иметь число в значении,
     * иначе параметр $value приобретет значение null
     * 
     * @param string $name - название поля
     * @param &$value - значение поля
     * @return boolean
     */
    public static function correctIDValue(string $name, &$value)
    {
        if (!preg_match('/_id$/i', $name)) return false;

        if (
            is_integer($value) && ($value > 0)
            || is_string($value) && preg_match('/^[1-9]\d*$/', $value)
        ) {
            $value = intval($value);

        } else {
            $value = 0;
        }
        return true;
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
        if (in_array(strtolower($name), ['id'])) return;

        foreach (static::correctionMethods() as $method) {
            if (forward_static_call_array([static::class, $method], [$name, &$value]))
                break;
        }

        if ($this->id  && !isset($this->oldParamData[$name]))
            $this->oldParamData[$name] = ['value' => $this->$name]; // иначе не будет работать со значением null

        parent::__set($name, $value);
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
    protected static function correctionMethods()
    {
        if (empty(self::$correctionMethods[static::class])) {
            self::$correctionMethods[static::class] = [];
            foreach (get_class_methods(static::class) as $method) {
                if (!preg_match('/^correct\w+value$/i', $method)) continue;

                self::$correctionMethods[static::class][] = $method;
            }
        }
        foreach (self::$correctionMethods[static::class] as $method) {
            yield $method;
        }
    }

    /**
     * Обновленный метод сохранения данных в БД
     *
     * @param $validate - параметр для родительского метода
     * @return mixed
     */
    public function save($validate = true)
    {
        $this->correctImortantFields();
        $this->oldParamData = [];
        return parent::save($validate);
    }

    /**
     * Поправляет важные поля в экземпляре, так как они не должны меняться при
     * изменении экземпляра
     * 
     * @return void
     */
    protected function correctImortantFields()
    {
        foreach (static::NOT_CHANGED_FIELDS as $fieldName) {
            if (!isset($this->oldParamData[$fieldName])) continue;

            $this->$fieldName = $this->oldParamData[$fieldName]['value'];
        }
    }

    /**
     * Проверяет не установлены ли для поля, чье имя передано, условия по наличию
     * конкретных значений в других полях экземпляра. Если условий по доступу к полю
     * нет или все условия позволяют доступ, то не будет ничего возвращено, иначе
     * будет возвращен массив с "ключом" value и "значением", равным null
     * 
     * @param string $name - название поля
     * @return null|array
     */
    protected function getFieldByConditions(string $name)
    {
        if (!isset(static::FIELD_EXISTENCE_CONDITIONS[$name]))
            return;

        foreach (static::FIELD_EXISTENCE_CONDITIONS[$name] as $field => $value) {
            if ($this->$field == $value) continue;

            return ['value' => null];
        }
    }

    /**
     * Поправленный метод получения значения конкретного поля из экземпляра
     * класса с помощью конструкции
     *     <экземпляр>-><поле>
     * 
     * @param $name - название поля
     * @return mixed
     */
    public function &__get($name)
    {
        foreach (['getFieldByConditions'] as $methodName) {
            $result = $this->$methodName($name);
            if (!is_array($result)) continue;

            return $result['value'];
        }
        return parent::__get($name);
    }

    /**
     * Обновленный метод удаления данных в БД
     * 
     * @return mixed
     */
    public function delete()
    {
        foreach (static::CHILD_NAMES_FOR_DELETING as $name) {
            foreach ($this->$name as $child) {
                $child->delete();
            }
        }
        return parent::delete();
    }

    /**
     * Создает копию объекта, используя те же значения полей, но исключая
     * поля
     *      'id', 'created_at'
     * и дополнительно поля из параметра $excludedFields
     * 
     * @param array $excludedFields - дополнительные поля, значения которых не
     * надо копировать в новый объект класса
     *
     * @return self
     */
    public function getPreparedCopyWithoutFields(array $excludedFields = []): self
    {
        $newContent = new static;
        array_push($excludedFields, 'id', 'created_at');
        foreach ($this->attributes() as $attribute => $value) {
            if (in_array($attribute, $excludedFields)) continue;
        
            $newContent->$attribute = $value;
        }
        return $newContent;
    }
};