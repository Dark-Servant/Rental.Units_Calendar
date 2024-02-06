<?
namespace Types;

/**
 * Класс для работы с названиями файлов
 */
class FileName
{
    protected $fullValue;
    protected $value;
    protected $nameValue = '';
    protected $extentionValue = '';

    /**
     * Undocumented function
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->prepareData($name);
    }
    
    /**
     * Undocumented function
     *
     * @param string $name
     * @return self
     */
    protected function prepareData(string $name): self
    {
        $this->fullValue = strtolower(trim(preg_replace('/\?.*$/', '', $name), '\\/'));
        $this->value = preg_match('/([^\/\\\\]+)$/i', $this->fullValue, $nameParts) ? $nameParts[1] : '';
        if (preg_match('/(.+?)(?:\.(\w+))?$/i', $this->value, $nameParts)) {
            $this->nameValue = $nameParts[1];
            $this->extentionValue = $nameParts[2] ?? '';
        }
        return  $this;
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
     * Undocumented function
     *
     * @return string
     */
    public function getNameValue(): string
    {
        return $this->nameValue;
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