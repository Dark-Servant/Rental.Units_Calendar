<?
namespace Types;

/**
 * Класс для работы с названиями файлов
 */
class FileName
{
    protected $fullValue;
    protected $value;
    protected $extentionValue;

    public function __construct(string $name)
    {
        $this->fullValue = strtolower(trim(preg_replace('/\?.*$/', '', $name), '\\/'));
        $this->value = preg_match('/([^\/\\\\]+)$/i', $this->fullValue, $nameParts) ? $nameParts[1] : '';
        $this->extentionValue = preg_match('/\.(\w+)$/i', $this->value, $nameParts) ? $nameParts[1] : '';
    }
    
    /**
     * Undocumented function
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getFullValue(): string
    {
        return $this->fullValue;
    }

    /**
     * Возвращает значение расширения
     *
     * @return string
     */
    public function getExtentionValue(): string
    {
        return $this->extentionValue;
    }
}