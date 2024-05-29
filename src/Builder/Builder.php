<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Builder;

use Exception;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\FieldTypeInterface;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\IndexFieldInterface;
use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;
use Redis;
use RedisException;
use Symfony\Component\Serializer\SerializerInterface;

class Builder implements BuilderInterface
{
    public array $arguments = [];

    public string $command;

    public IndexInterface $index;

    public int $limit = 10000;

    public int $offset = 0;

    public array $select = [];

    public array $wheres = [];

    public int $firstKey = 1;

    public int $secondKey = 1;

    public array $sort = [];

    public array $aggregateSort = [];

    private bool $enableClear = true;

    public int $total = 0;

    public array $groupBys = [];

    private static array $reservedSpecialCharacters = [
        '.',
        '-',
        '@',
        '%',
        '"',
        ':',
        ';',
        '[',
        ']',
        '{',
        '}',
        '(',
        ')',
        '|',
    ];

    /**
     * @param RedisConnection     $connection
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly RedisConnection $connection,
        private readonly SerializerInterface $serializer,
    )
    {
    }

    /**
     * @param array<FieldTypeInterface> $columns
     * @return BuilderInterface
     * @throws Exception Exception.
     */
    public function select(array $columns): BuilderInterface
    {
        foreach ($columns as $column) {
            if (!$column instanceof FieldTypeInterface) {
                throw new Exception('Selected column must be instance of FieldTypeInterface!');
            }

            $this->select[] = $column->getAlias();
        }

        return $this;
    }

    /**
     * @return BuilderInterface
     */
    public function getNewInstance(): BuilderInterface
    {
        return new self($this->connection, $this->serializer);
    }

    /**
     * @param IndexInterface $index
     * @param integer|null   $childDeepLevel
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function createIndex(IndexInterface $index, ?int $childDeepLevel = 1): bool
    {
        $this->command   = 'FT.CREATE';
        $this->arguments = $this->getColumnQuery(
            $index,
            [$index->getIndexName(), 'ON', 'JSON', 'PREFIX', 1, $index->getPrefix(), 'SCHEMA'],
            deep: $childDeepLevel,
        );

        return $this->execute();
    }

    /**
     * @param string  $indexPrefix
     * @param integer $id
     * @param string  $value
     * @return false|mixed|Redis
     * @throws RedisException RedisException.
     */
    public function saveJsonDocument(string $indexPrefix, int $id, string $value): mixed
    {
        $this->command   = 'JSON.SET';
        $this->arguments = [
            sprintf('%s:%s', $indexPrefix, $id),
            '$',
            $value,
        ];

        return $this->execute();
    }

    /**
     * @param FieldTypeInterface $field
     * @param string|null        $direction
     * @return BuilderInterface
     */
    public function sortBy(FieldTypeInterface $field, ?string $direction = 'asc'): BuilderInterface
    {
        $this->sort      = ['SORTBY', $field->getAlias(), $direction];
        $this->firstKey  = 1;
        $this->secondKey = 3;

        return $this;
    }

    /**
     * @param FieldTypeInterface $field
     * @param string|null        $direction
     * @return BuilderInterface
     */
    public function aggregateSort(FieldTypeInterface $field, ?string $direction = 'asc'): BuilderInterface
    {
        $alias = '@' . $field->getAlias();
        if (!in_array($alias, $this->aggregateSort)) {
            array_push($this->aggregateSort, $alias, $direction);
        }

        return $this;
    }

    /**
     * @param IndexInterface $index
     * @return mixed|Redis
     * @throws RedisException RedisException.
     */
    public function save(IndexInterface $index): mixed
    {
        return $this->saveJsonDocument(
            $index->getPrefix(),
            $index->getId(),
            $this->serializer->serialize($index, 'json')
        );
    }

    /**
     * @param IndexInterface $index
     * @return array
     * @throws RedisException RedisException.
     */
    public function getInfo(IndexInterface $index): array
    {
        $this->command   = 'FT.INFO';
        $this->arguments = [$index->getIndexName()];

        return $this->execute();
    }

    /**
     * @param IndexInterface $index
     * @return BuilderInterface
     */
    public function searchOn(IndexInterface $index): BuilderInterface
    {
        $this->command   = 'FT.SEARCH';
        $this->arguments = [$index->getIndexName()];
        $this->index     = $index;

        return $this;
    }

    /**
     * @param integer      $limit
     * @param integer|null $offset
     * @return BuilderInterface
     */
    public function limit(int $limit, ?int $offset = 0): BuilderInterface
    {
        $this->limit  = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param FieldTypeInterface $fieldType
     * @param integer|float|null $min
     * @param integer|float|null $max
     * @return BuilderInterface
     */
    public function whereBetweenNumbers(
        FieldTypeInterface $fieldType,
        int|float|null $min = null,
        int|float|null $max = null,
    ): BuilderInterface
    {
        if (is_null($min)) {
            $min = '-inf';
        }

        if (is_null($max)) {
            $max = '+inf';
        }

        $this->wheres[] = '@' . $fieldType->getAlias() . ':[' . $min . ',' . $max . ']';

        return $this;
    }

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function aggregate(): array
    {
        // TODO: the function doesn't cover all situations for now, make it better.
        $this->command = 'FT.AGGREGATE';
        $this->processWheres();
        if (!empty($this->groupBys)) {
            array_push($this->arguments, 'GROUPBY', count($this->groupBys), ...$this->groupBys);
            if (!empty($this->select)) {
                foreach ($this->select as $item) {
                    array_push(
                        $this->arguments,
                        'REDUCE',
                        'TOLIST',
                        1,
                        '@' . $item,
                        'AS',
                        $item,
                    );
                }
            }
        }

        if (!empty($this->aggregateSort)) {
            foreach ($this->aggregateSort as $index => $item) {
                if ($index & 1 || in_array(str_replace('@', '', $item), $this->select)) {
                    continue;
                }
                array_push(
                    $this->arguments,
                    'REDUCE',
                    'TOLIST',
                    1,
                    $item,
                    'AS',
                    str_replace('@', '', $item),
                );
            }

            array_push(
                $this->arguments,
                'SORTBY',
                count($this->aggregateSort),
                ...$this->aggregateSort
            );
        }
        $this->processLimit();
        $data = $this->execute();
        $this->total  = (int)array_shift($data);
        $result = [];
        foreach ($data as $datum) {
            $value = [];
            foreach ($datum as $key => $item) {
                if ($key & 1) {
                    continue;
                }

                $value[$item] = $datum[$key + 1];
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param array<FieldTypeInterface> $fields
     * @return BuilderInterface
     */
    public function groupBy(array $fields): BuilderInterface
    {
        foreach ($fields as $field) {
            $this->groupBys[] = '@' . $field->getAlias();
        }

        return $this;
    }

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function get(): array
    {
        $this->processWheres();
        $hasSelection = false;
        if (!empty($this->select)) {
            $hasSelection = true;
            array_push($this->arguments, 'RETURN', count($this->select), ...$this->select);
        }

        $this->processSort();
        $this->processLimit();
        $firstKey = $this->firstKey;
        $secondKey = $this->secondKey;
        $result = $this->execute();
        if ($hasSelection) {
            return $this->makeResultBySelection($result);
        }

        return $this->makeResult($result, $firstKey, $secondKey);
    }

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function getIndexList(): array
    {
        $this->command = 'FT._LIST';
        $this->arguments = [];

        return $this->execute();
    }

    /**
     * @param IndexInterface $index
     * @return string|false
     * @throws RedisException RedisException.
     */
    public function getDocument(IndexInterface $index): string|bool
    {
        $this->command   = 'JSON.GET';
        $this->arguments = [$index->getPrefix() . ':' . $index->getId()];

        return $this->execute();
    }

    /**
     * @param IndexInterface $index
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function dropIndex(IndexInterface $index): bool
    {
        $this->command   = 'FT.DROP';
        $this->arguments = [$index->getIndexName()];

        return $this->execute();
    }

    /**
     * @param FieldTypeInterface $field
     * @param array              $values
     * @return BuilderInterface
     */
    public function whereInNumbers(FieldTypeInterface $field, array $values): BuilderInterface
    {
        if (empty($values)) {
            return $this;
        }

        $aliasField = $field->getAlias();
        foreach ($values as $value) {
            $wheres[] = '@' . $aliasField . ':[' . $value . ',' . $value . ']';
            $wheres[] = '|';
        }
        array_pop($wheres);
        $this->wheres[] = implode('', $wheres);

        return $this;
    }

    /**
     * @param FieldTypeInterface $field
     * @param array              $values
     * @return BuilderInterface
     */
    public function whereInArrayText(FieldTypeInterface $field, array $values): BuilderInterface
    {
        if (empty($values)) {
            return $this;
        }

        $aliasField = $field->getAlias();
        foreach ($values as $value) {
            $wheres[] = '@' . $aliasField . ':(' . $value . ',' . $value . ')';
            $wheres[] = '|';
        }
        array_pop($wheres);
        $this->wheres[] = implode('', $wheres);

        return $this;
    }

    /**
     * @param IndexInterface $index
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function deleteDocument(IndexInterface $index): bool
    {
        $this->command   = 'JSON.DEL';
        $this->arguments = [$index->getPrefix() . ':' . $index->getId()];

        return (bool)$this->execute();
    }

    /**
     * @return BuilderInterface
     */
    public function disableClear(): BuilderInterface
    {
        $this->enableClear = false;

        return $this;
    }

    /**
     * @param FieldTypeInterface $field
     * @param string             $value
     * @return $this
     */
    public function whereTextFieldLike(FieldTypeInterface $field, string $value): BuilderInterface
    {
        $this->wheres[] = '@' . $field->getAlias() . ':(' . $this->cleanString($value) . ')';

        return $this;
    }

    /**
     * @param FieldTypeInterface $field
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function max(FieldTypeInterface $field): mixed
    {
        return $this->getMaxOrMin($field, 'MAX');
    }

    /**
     * @param FieldTypeInterface $field
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function min(FieldTypeInterface $field): mixed
    {
        return $this->getMaxOrMin($field, 'MIN');
    }

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function getFirst(): array
    {
        return $this->limit(1)->get()[0];
    }

    /**
     * @param FieldTypeInterface $field
     * @param string             $reduceFunctionName
     * @return mixed|null
     * @throws RedisException RedisException.
     */
    private function getMaxOrMin(FieldTypeInterface $field, string $reduceFunctionName): mixed
    {
        $this->command = 'FT.AGGREGATE';
        $this->processWheres();
        array_push(
            $this->arguments,
            'GROUPBY',
            0,
            'REDUCE',
            $reduceFunctionName,
            1,
            '@' . $field->getAlias()
        );
        $this->processSort();
        $this->processLimit();
        $result = $this->execute();

        return $result ? $result[1][1] : null;
    }

    /**
     * @param array $data
     * @return array
     */
    private function makeResultBySelection(array $data): array
    {
        $this->total  = (int)array_shift($data);
        $result = [];
        if (!$this->total) {
            return $result;
        }
        foreach ($data as $index => $datum) {
            if (! ($index & 1)) {
                continue;
            }
            $key = null;
            $resultItem = [];
            foreach ($datum as $index => $item) {
                if (! ($index & 1)) {
                    $key = $item;
                    continue;
                }

                $resultItem[$key] = $item;
            }

            $result[] = $resultItem;
        }

        return $result;
    }

    /**
     * @param array   $data
     * @param integer $firstKey
     * @param integer $secondKey
     * @return array
     */
    private function makeResult(array $data, int $firstKey, int $secondKey): array
    {
        $result       = [];
        $this->total  = (int)array_shift($data);
        foreach ($data as $index => $key) {
            if (! ($index & $firstKey)) {
                continue;
            }

            $result[] = json_decode($key[$secondKey], true);
        }

        return $result;
    }

    /**
     * @param IndexInterface $index
     * @param array          $parameters
     * @param string|null    $prefix
     * @param integer        $deep
     * @param string         $aliasPrefix
     * @return array
     */
    private function getColumnQuery(
        IndexInterface $index,
        array $parameters,
        ?string $prefix = '',
        int $deep = 1,
        string $aliasPrefix = '',
    ): array
    {
        $reflectionClass      = new \ReflectionClass($index);
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes();
            foreach ($attributes as $attribute) {
                $field = $attribute->newInstance();
                assert($field instanceof FieldTypeInterface);
                if ($field instanceof IndexFieldInterface) {
                    if ($index == $field->getIndex()) {
                        $aliasPrefix = 'ChildLevel' . $deep;
                        $deep--;
                        if ($deep < 0) {
                            continue;
                        }
                    }
                    $parameters = $this->getColumnQuery(
                        $field->getIndex(),
                        $parameters,
                        $prefix . $field->getAlias() . $field->getIndexDelimiter(),
                        $deep,
                        $aliasPrefix
                    );
                    continue;
                }

                array_push(
                    $parameters,
                    sprintf('$.%s%s', $prefix, $field->getName()),
                    'AS',
                    $field->getAlias() . $aliasPrefix,
                    $field->getType(),
                    'SORTABLE',
                );
            }
        }

        return $parameters;
    }

    /**
     * @return false|mixed|Redis
     * @throws RedisException RedisException.
     */
    private function execute(): mixed
    {
        // TODO: it must return a QueryBuilder Object that depends to the query.
        $result = $this->connection->run($this->command, $this->arguments);
        $this->clear();

        return $result;
    }

    /**
     * @return void
     */
    private function clear(): void
    {
        if ($this->enableClear) {
            $this->arguments = [];
            $this->command = '';
            $this->limit = 1000;
            $this->offset = 0;
            $this->select = [];
            $this->wheres = [];
            $this->firstKey = 1;
            $this->secondKey = 1;
            $this->sort = [];
            $this->groupBys = [];
            $this->searchOn($this->index);
        }
    }

    /**
     * @param string $string
     * @return string
     */
    private function cleanString(string $string): string
    {
        $string = str_replace(self::$reservedSpecialCharacters, ' ', $string);
        $result = [];
        foreach (explode(' ', $string) as $item) {
            if (!is_numeric($item)) {
                $result[] = $item;
            }
        }

        return implode(' ', $result);
    }

    /**
     * @return void
     */
    private function processLimit(): void
    {
        array_push($this->arguments, 'LIMIT', $this->offset, $this->limit);
    }

    /**
     * @return void
     */
    private function processSort(): void
    {
        array_push($this->arguments, ...$this->sort);
    }

    /**
     * @return void
     */
    private function processWheres(): void
    {
        if (empty($this->wheres)) {
            $where = '*';
        } else {
            $where = '(' . implode(') (', $this->wheres) . ')';
        }

        array_push($this->arguments, $where);
    }
}
