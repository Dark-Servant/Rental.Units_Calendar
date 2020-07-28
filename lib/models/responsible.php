<?

class Responsible extends InfoserviceModel
{
    static $has_many = [
        ['comments'],
        ['contents']
    ];

    /**
     * По полученным данным пользователя создает в базе запись, если ее нет,
     * и возвращает экземпляр класса Responsible. В данных пользователя
     * должен быть обязательно указан параметр ID - внешний идентификатор
     * пользователя, с которым будут связываться данные пользователя в базе,
     * а так же могут быть указаны, но не обязательно, параметры LAST_NAME,
     * NAME, SECOND_NAME и EMAIL
     * 
     * @param array $data - данные пользователя
     * @return Responsible
     */
    public static function initialize(array $data)
    {
        global $langValues;

        if (
            !isset($data['ID']) || !($userId = intval($data['ID']))
        ) throw new Exception($langValues['ERROR_EMPTY_USER_ID']);

        $responsible = self::find_by_external_id($userId);
        if (!$responsible) $responsible = new self;

        $responsible->external_id = $userId;
        $nameValue = '';
        foreach (['LAST_NAME', 'NAME', 'SECOND_NAME'] as $namePart) {
            $partvalue = trim($data[$namePart]);
            if (!$partvalue) continue;

            $nameValue .= ($nameValue ? ' ' : '') . $partvalue;
        }
        $responsible->name = trim($nameValue) ?: strval($data['EMAIL']);
        $responsible->save();
        return $responsible;
    }
};