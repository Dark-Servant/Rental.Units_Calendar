<?
namespace SQL\Models\Relative;

use ActiveRecord\Model;
use Types\FileName;
use SQL\TableSet;
use Log\Base as Logger;

class Belonging implements \IteratorAggregate
{
    protected $name;
    protected $tableName = false;
    protected $belongs_to = [];

    public function __construct(string $name)
    {
        $this->initMainDataByName($name);
    }

    protected function initMainDataByName(string $name): self
    {
        $this->name = strtolower(trim($name));
        if (!in_array(Model::class, class_parents($this->name)))
            return $this;

        $this->tableName = $this->name::table_name();
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTableName(): ?string
    {
        return $this->tableName ?: null;
    }

    public function isExists(): bool
    {
        return $this->tableName !== false;
    }
 
    public function initData(): self
    {
        if (!$this->isExists())
            return $this;

        $pathValue = dirname((new \ReflectionClass($this->name))->getFileName());
        foreach (glob($pathValue . '/*') as $modelFile) {
            if (is_dir($modelFile)) continue;

            $name = (new FileName($modelFile))->getNameValue();
            $this->addFromModelByClassName($name);
        }

        Logger::getMainInstance()->addNextValues(
            '******* ' . __METHOD__,
            $this->belongs_to
        );

        return $this;
    }

    public function addFromModelByClassName(string $className): self
    {
        $classUnit = new \ReflectionClass($className);
        if (!isset($classUnit->getStaticProperties()['belongs_to']))
            return $this;

        $fieldExistenceConditions = [];
        if (isset($classUnit->getConstants()['FIELD_EXISTENCE_CONDITIONS']))
            $fieldExistenceConditions = $className::FIELD_EXISTENCE_CONDITIONS;

        $tableUnit = $className::table();
        $tableName = $className::table_name();
        foreach ($className::$belongs_to as $link) {
            $fieldCode = current($link);
            $relation = $tableUnit->get_relationship($fieldCode);
            if ($relation->class_name::table_name() != $this->tableName) continue;

            $linkResult = [
                'field' => current($relation->foreign_key)
            ];
            if (isset($fieldExistenceConditions[$fieldCode]))
                $linkResult['conditions'] = $fieldExistenceConditions[$fieldCode];

            $this->belongs_to[$tableName][] = $linkResult;
        }
        return $this;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->getGeneratorWithTableNameAndDescriptions();
    }

    public function getGeneratorOfTableSetByValue($value): \Generator
    {
        foreach ($this->getGeneratorWithTableNameAndDescriptions() as $tableName => $descriptions) {
            foreach ($descriptions as $description) {
                yield (new TableSet($tableName))
                        ->setAsConditionNextFieldValues(static::getFieldValuesByValueViaDescription($value, $description))
                        ->setFieldsForUpdating([$description['field']]);
            }
        }
    }

    public function getGeneratorWithTableNameAndDescriptions(): \Generator
    {
        foreach ($this->belongs_to as $tableName => $descriptions) {
            yield $tableName => $descriptions;
        }
    }

    public static function getFieldValuesByValueViaDescription($value, array $description): ?array
    {
        if (!is_string($description['field'])) return [];

        $result = [
            $description['field'] => $value
        ];
        return is_array($description['conditions'])
             ? $result + $description['conditions']
             : $result;
    }
}
