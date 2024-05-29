<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Repositories;

use Exception;
use TohidHabiby\RedisIntendedJsonSearch\Builder\BuilderInterface;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\FieldTypeInterface;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\IndexFieldInterface;
use TohidHabiby\RedisIntendedJsonSearch\Filter\FilterInterface;
use TohidHabiby\RedisIntendedJsonSearch\Indices\Collection;
use TohidHabiby\RedisIntendedJsonSearch\Indices\Index;
use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;
use RedisException;
use ReflectionException;

abstract class AbstractRepository implements RepositoryInterface
{
    private const DEFAULT_PER_PAGE = 5;

    /** @var IndexInterface */
    private IndexInterface $index;

    /**
     * @param BuilderInterface $builder
     */
    public function __construct(
        public readonly BuilderInterface $builder,
    )
    {
        $this->index = $this->newInstance();
        $this->builder->searchOn($this->index);
    }

    /**
     * @return IndexInterface
     */
    public function getModel(): IndexInterface
    {
        return $this->index;
    }

    /**
     * @return IndexInterface
     */
    abstract public function newInstance(): IndexInterface;

    /**
     * @param integer|null $childDeepLevel
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function create(?int $childDeepLevel = 1): bool
    {
        return $this->builder->createIndex($this->index, $childDeepLevel);
    }

    /**
     * @param array $data
     * @return RepositoryInterface
     */
    public function fill(array $data): RepositoryInterface
    {
        $reflectionClass = new \ReflectionClass($this->index);
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            $value = $data[$name] ?? null;
            // Redis Stack can not search a document which has an empty array in it's variables.
            if (is_array($value) && empty($value)) {
                $value = null;
            }

            $this->index->$name = $value;
        }

        return $this;
    }

    /**
     * @param array $data
     * @return RepositoryInterface
     */
    public function fillWithRelations(array $data): RepositoryInterface
    {
        return $this->fill($data);
    }

    /**
     * @param array $data
     * @return IndexInterface
     * @throws RedisException RedisException.
     */
    public function saveWithRelations(array $data): IndexInterface
    {
        return $this->fillWithRelations($data)->save();
    }

    /**
     * @param integer $id
     * @return array|boolean
     * @throws RedisException RedisException.
     */
    public function getDocumentById(int $id): array|bool
    {
        $this->index->id = $id;
        $document        = $this->builder->searchOn($this->index)->getDocument($this->index);

        return $document ? json_decode($document, true) : $document;
    }

    /**
     * @return IndexInterface
     * @throws RedisException RedisException.
     */
    public function save(): IndexInterface
    {
        $this->builder->save($this->index);

        return $this->index;
    }

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function getInfo(): array
    {
        return $this->builder->getInfo($this->index);
    }

    /**
     * @param string $name
     * @return FieldTypeInterface
     * @throws Exception Exception.
     */
    public function getFieldByName(string $name): FieldTypeInterface
    {
        $index = $this->index;
        if (strpos($name, '.') !== false) {
            $relations = explode('.', $name);
            $count = count($relations);
            while (1 < $count) {
                $propertyName = array_shift($relations);
                $index = $this->getIndexByPropertyName($index, $propertyName);
                $count = count($relations);
            }

            $name = array_shift($relations);
        }

        $reflectionClass      = new \ReflectionClass($index);
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes();
            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if ($instance->getName() == $name) {
                    return $instance;
                }
            }
        }

        throw new Exception('The field is not exists!');
    }

    /**
     * @param string     $class
     * @param array|null $data
     * @return Collection|null
     * @throws ReflectionException ReflectionException.
     */
    protected function makeCollection(string $class, null|array $data): ?Collection
    {
        if (empty($data)) {
            return null;
        }

        $collection = new Collection();
        foreach ($data as $item) {
            $collection->add($this->getRepositoryByIndexClass($class)->fill($item)->getModel());
        }

        return $collection;
    }

    /**
     * @param IndexInterface $index
     * @param string         $propertyName
     * @return IndexInterface
     */
    private function getIndexByPropertyName(IndexInterface $index, string $propertyName): IndexInterface
    {
        $reflectionClass      = new \ReflectionClass($index);
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->getName() == $propertyName) {
                $attributes = $reflectionProperty->getAttributes();
                foreach ($attributes as $attribute) {
                    $instance = $attribute->newInstance();
                    if ($instance instanceof IndexFieldInterface && $instance->getName() == $propertyName) {
                        return $instance->getIndex();
                    }
                }
            }
        }

        return $index;
    }

    /**
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function drop(): bool
    {
        return $this->builder->dropIndex($this->index);
    }

    /**
     * @param integer $id
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function deleteById(int $id): bool
    {
        $this->index->id = $id;

        return $this->builder->deleteDocument($this->index);
    }

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function all(): array
    {
        return $this->builder->searchOn($this->index)->get();
    }

    /**
     * @return integer
     */
    public function getTotalCount(): int
    {
        return $this->builder->total;
    }

    /**
     * @param array $ids
     * @return array
     * @throws RedisException RedisException.
     */
    public function getByIds(array $ids): array
    {
        return $this->builder->searchOn($this->index)
            ->whereInNumbers($this->getFieldByName('id'), $ids)
            ->get();
    }

    /**
     * @param integer|null $offset
     * @param integer|null $perPage
     * @return array
     * @throws RedisException RedisException.
     */
    public function paginate(?int $offset = 0, ?int $perPage = null): array
    {
        if ($offset < 0) {
            $offset = 0;
        }

        if ($perPage < 1) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        return $this->builder->searchOn($this->index)
            ->limit($perPage, $offset)
            ->get();
    }

    /**
     * @param FilterInterface $filter
     * @return RepositoryInterface
     */
    public function filter(FilterInterface $filter): RepositoryInterface
    {
        return $filter->setRepository($this)->apply();
    }

    /**
     * @param string $className
     * @return RepositoryInterface
     * @throws \ReflectionException ReflectionException.
     * @throws Exception Exception.
     */
    public function getRepositoryByIndexClass(string $className): RepositoryInterface
    {
        $reflection = new \ReflectionClass($className);
        $attributes = $reflection->getAttributes();
        $repository = null;
        foreach ($attributes as $attribute) {
            if ($attribute->getName() != Index::class) {
                continue;
            }
            $repositoryName = $attribute->getArguments()[0];
            $repository     = new $repositoryName($this->builder->getNewInstance());
            break;
        }

        if (! $repository) {
            throw new Exception('Repository not found!');
        }

        return $repository;
    }

    /**
     * @return BuilderInterface
     */
    public function getBuilder(): BuilderInterface
    {
        return $this->builder;
    }

    /**
     * @param string $fieldName
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function getMax(string $fieldName): mixed
    {
        return $this->builder->searchOn($this->getModel())->max($this->getFieldByName($fieldName));
    }

    /**
     * @param string $fieldName
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function getMin(string $fieldName): mixed
    {
        return $this->builder->searchOn($this->getModel())->min($this->getFieldByName($fieldName));
    }
}
