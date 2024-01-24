<?
namespace Types;

class StringType
{
    /**
     * Возвращает значение, в котором будут все символы букв и цифр, указанных в значении 
     * параметра $value, остальные символы, будь они группой, т.е. идут друг за другом, или
     * просто по одиночке, заменятся на одно значение параметра $spaceSymbol. Все буквы в
     * итоговом значении будут так же приведены к нижнему регистру
     * 
     * @param string $value - значение, которое надо преобразовать
     * @param string $spaceSymbol - символ, на который надо заменить символы не букв и не цифр
     *
     * @return string
     */
    public static function getSimpleValue(string $value, string $spaceSymbol = ' ')
    {
        return trim(preg_replace('/[^a-zа-я\d]+/iu', $spaceSymbol, mb_strtolower($value)));
    }

    /**
     * Выполняет работу метода getSimpleValue сразу для всех элементов из параметра $values,
     * далее возвращает результат.
     *
     * @param array $values - элементы, значения которых надо провести через метод getSimpleValue
     * @param $keyCode - ключ, если элементы параметра $values массивы, то передает методу getSimpleValue
     * значения этих массивов под этим ключом
     * @param string $spaceSymbol - аналогичный параметр, как в getSimpleValue
     *
     * @return array
     */
    public static function getSimpleValues(array $values, $keyCode = null, string $spaceSymbol = ' ')
    {
        $result = [];
        foreach ($values as $value) {
            if (is_string($value)) {
                $result[] = static::getSimpleValue($value, $spaceSymbol);

            } elseif (is_array($value)) {
                $result[] = static::getSimpleValue($value[is_string($keyCode) || is_numeric($keyCode) ? $keyCode : 0], $spaceSymbol);
            }
        }
        return $result;
    }

    /**
     * Возвращает camelCase-вид переданного значения, где первые буквы каждой буквенной последовательности
     * приведены к верхнему регистру, а все символы, идущие перед каждой такой последовательностью, либо
     * удалены, если значение параметра $deleteUnword передано со значением true, либо оставлены
     *
     * @param string $value - значение, которое надо привест к camelCase-виду
     * @param bool $deleteUnword - удалять ли небуквенные последовательности перед каждой буквенной,
     * по-умолчанию, равно true
     *
     * @return string
     */
    public static function getCamelCaseValue(string $value, bool $deleteUnword = true)
    {
        return preg_replace_callback(
                    '/([^a-z]+)([a-z])/i',
                    function($valueParts) use($deleteUnword) {
                        return ($deleteUnword ? '' : $valueParts[1]) . strtoupper($valueParts[2]);
                    },
                    strtolower($value)
                );
    }
    
    /**
     * Возвращает результат метода getCamelCaseValue, которому было передано название файла
     * без указанного к нему пути и расширения.
     * 
     * !ВНИМАНИЕ! Если в названии файла есть точки, кроме той, что отделяет расширение, то
     * стоит указать и расширение файла
     *
     * @param string $value - путь к файлу с названием файла и его расширением
     * @param bool $deleteUnword - удалять ли небуквенные последовательности перед каждой буквенной,
     * по-умолчанию, равно true
     *
     * @return string
     */
    public static function getCamelCaseFileName(string $path, bool $deleteUnword = true)
    {
        return static::getCamelCaseValue(preg_replace('/\.[^\.]+$/', '', basename($path)), $deleteUnword);
    }
}