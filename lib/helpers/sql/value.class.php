<?
namespace SQL;

class Value
{
    protected $value = null;

    public function __construct($value)
    {
        $this->initValue($value);
    }

    public function initValue($value): self
    {
        if (is_array($value)) {
            $this->value = count($value) > 1 ? static::getMultiValue($value) : static::getSingleValue($value[0]);

        } else {
            $this->value = static::getSingleValue($value);
        }
        return $this;
    }

    public static function getMultiValue(array $value): ?array
    {
        return array_filter(
                    array_map(
                        function($val) {
                            return static::getSingleValue($val);
                        },
                        $value
                    )
                );
    }

    public static function getSingleValue($value)
    {
        return is_string($value) || is_numeric($value) || is_bool($value) ? $value : null;
    }

    public function getResultForField(string $fieldCode): ?string
    {
        if (!isset($this->value)) {
            return $fieldCode . ' IS NULL';

        } elseif (is_array($this->value)) {
            return $fieldCode . ' IN ("' . implode('", "', $this->value) . '")';
            
        } else {
            return $fieldCode . ' = "' . addslashes(strval($this->value)) . '"';
        }
    }
}