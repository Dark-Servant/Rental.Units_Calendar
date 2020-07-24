<?

class Content extends InfoserviceModel
{
    static $belongs_to = [
        ['technic'],
        ['responsible'],
        ['customer'],
    ];

    /**
     * Информация о классах контента. Используется при выводе в календаре, 
     * чтобы окрасить в конкретный цвет.
     * В параметре status контента хранится число, которое указывает на
     * порядковый номер класса из этой константы, только если оно больше,
     * чем порядковый номер элемента final, то таким статус и остается, 
     * т.е. final
     */
    const CONTENT_DEAL_STATUS = [
        'waiting', 'process', 'final', 'closed', 'many'
    ];

    /**
     * Регулярные выражения для установки по текстовому значению параметра status
     * у контента реального его числового значения, которое будет порядковым номером
     * подошедшего регулярного выражения. Это значение так же будет указывать на
     * порядковый номер элемента из константы CONTENT_DEAL_STATUS. Если ни одно из
     * регулярных выражение не определило статус, то статус будет равен длине массива, т.е,
     * как указано в комментарии к константе CONTENT_DEAL_STATUS, затем восприниматься
     * как final
     */
    const CONTENT_DEAL_STATUS_REGEX = [
        '/\b(?:нов[аяйоые]+|резерв) +/iu',
        '/\bпроведение +/iu',
        '/\b(?:финал[ьнаяйоые]*|закрыт[аяыйое]*|заверш[иаолуеють]+)\b/iu'
    ];

    /**
     * Обработчик изменения параметра status. Если параметр будет иметь строковое значение,
     * то благодаря константе CONTENT_DEAL_STATUS_REGEX будет заменено на числовое
     * 
     * @param $name - название поля
     * @param &$value - значение поля
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