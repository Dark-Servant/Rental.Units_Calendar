<?
namespace SQL;

class TableSet
{
    protected $name;
    protected $fieldCodes = [];
    protected $conditions = '';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setAsConditionNextFieldValues(array $fieldValues): self
    {
        $this->conditions = '';
        foreach ($fieldValues as $fieldCode => $fieldValue) {
            $this->conditions .= (empty($this->conditions) ? '' : ' AND ')
                               . '(' . (new Value($fieldValue))->getResultForField($fieldCode) . ')';
        }
        return $this;
    }

    public function setFieldsForUpdating(array $fieldCodes): self
    {
        $this->fieldCodes = [];
        foreach ($fieldCodes as $fieldCode) {
            if (!is_string($fieldCode)) continue;

            $fieldCode = static::getPreparedCodeFromFieldCode($fieldCode);
            if (in_array($fieldCode, $this->fieldCodes)) continue;

            $this->fieldCodes[] = $fieldCode;
        }
        return $this;
    }

    public function updateFieldsByValues(array $values): self
    {
        if (empty($this->conditions)) return $this;

        $updatingFields = $this->getListOfSQLCodeForFieldValues($values);
        if (empty($updatingFields)) return $this;

        (new Query(sprintf('UPDATE `%s` SET %s WHERE %s', $this->name, implode(', ', $updatingFields), $this->conditions)))->send();
        return $this;
    }

    public function getListOfSQLCodeForFieldValues(array $values): array
    {
        $result = [];
        foreach ($values as $code => $value) {
            if (is_string($code)) {
                $fieldCode = static::getPreparedCodeFromFieldCode($code);
                if (!empty($this->fieldCodes) && !in_array($fieldCode, $this->fieldCodes)) continue;

            } elseif (isset($this->fieldCodes[$code])) {
                $fieldCode = $this->fieldCodes[$code];
            }
            $result[] = (new Value($value))->getResultForField($fieldCode);
        }
        return $result;
    }

    public function deleteData(): self
    {
        if (empty($this->conditions)) return $this;

        (new Query(sprintf('DELETE FROM `%s` WHERE %s', $this->name, $this->conditions)))->send();
        return $this;
    }

    public static function getPreparedCodeFromFieldCode(string $fieldCode): string
    {
        return strtolower(trim(preg_replace('/\W+/', '', $fieldCode)));
    }
}