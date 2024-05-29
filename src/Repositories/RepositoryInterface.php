<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Repositories;

use Exception;
use TohidHabiby\RedisIntendedJsonSearch\Builder\BuilderInterface;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\FieldTypeInterface;
use TohidHabiby\RedisIntendedJsonSearch\Filter\FilterInterface;
use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;
use RedisException;
use ReflectionException;

interface RepositoryInterface
{
    /**
     * @return IndexInterface
     */
    public function getModel(): IndexInterface;

    /**
     * @return IndexInterface
     */
    public function newInstance(): IndexInterface;

    /**
     * @param integer|null $childDeepLevel
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function create(?int $childDeepLevel = 1): bool;

    /**
     * @param array $data
     * @return RepositoryInterface
     */
    public function fill(array $data): RepositoryInterface;

    /**
     * @return IndexInterface
     * @throws RedisException RedisException.
     */
    public function save(): IndexInterface;

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function getInfo(): array;

    /**
     * @param string $name
     * @return FieldTypeInterface
     * @throws Exception Exception.
     */
    public function getFieldByName(string $name): FieldTypeInterface;

    /**
     * @param array $data
     * @return IndexInterface
     * @throws ReflectionException ReflectionException.
     */
    public function saveWithRelations(array $data): IndexInterface;

    /**
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function drop(): bool;

    /**
     * @param integer $id
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function deleteById(int $id): bool;

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function all(): array;

    /**
     * @return integer
     */
    public function getTotalCount(): int;

    /**
     * @param array $ids
     * @return array
     * @throws RedisException RedisException.
     */
    public function getByIds(array $ids): array;

    /**
     * @param array $data
     * @return RepositoryInterface
     */
    public function fillWithRelations(array $data): RepositoryInterface;

    /**
     * @param integer|null $offset
     * @param integer|null $perPage
     * @return array
     * @throws RedisException RedisException.
     */
    public function paginate(?int $offset = 1, ?int $perPage = null): array;

    /**
     * @param FilterInterface $filter
     * @return RepositoryInterface
     */
    public function filter(FilterInterface $filter): RepositoryInterface;

    /**
     * @param string $className
     * @return RepositoryInterface
     * @throws \ReflectionException ReflectionException.
     * @throws Exception Exception.
     */
    public function getRepositoryByIndexClass(string $className): RepositoryInterface;

    /**
     * @return BuilderInterface
     */
    public function getBuilder(): BuilderInterface;

    /**
     * @param string $fieldName
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function getMax(string $fieldName): mixed;

    /**
     * @param string $fieldName
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function getMin(string $fieldName): mixed;
}
