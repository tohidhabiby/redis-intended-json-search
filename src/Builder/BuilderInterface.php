<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Builder;

use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\FieldTypeInterface;
use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;
use RedisException;

interface BuilderInterface
{
    /**
     * @param array $columns
     * @return BuilderInterface
     */
    public function select(array $columns): BuilderInterface;

    /**
     * @return BuilderInterface
     */
    public function getNewInstance(): BuilderInterface;

    /**
     * @param IndexInterface $index
     * @param integer|null   $childDeepLevel
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function createIndex(IndexInterface $index, ?int $childDeepLevel = 1): bool;

    /**
     * @param string  $indexPrefix
     * @param integer $id
     * @param string  $value
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function saveJsonDocument(string $indexPrefix, int $id, string $value): mixed;

    /**
     * @param FieldTypeInterface $field
     * @param string|null        $direction
     * @return BuilderInterface
     */
    public function aggregateSort(FieldTypeInterface $field, ?string $direction = 'asc'): BuilderInterface;

    /**
     * @param IndexInterface $index
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function save(IndexInterface $index): mixed;

    /**
     * @param IndexInterface $index
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function getInfo(IndexInterface $index): array;

    /**
     * @param IndexInterface $index
     * @return BuilderInterface
     */
    public function searchOn(IndexInterface $index): BuilderInterface;

    /**
     * @param integer      $limit
     * @param integer|null $offset
     * @return BuilderInterface
     */
    public function limit(int $limit, ?int $offset = 0): BuilderInterface;

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
    ): BuilderInterface;

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function aggregate(): array;

    /**
     * @param array<FieldTypeInterface> $fields
     * @return BuilderInterface
     */
    public function groupBy(array $fields): BuilderInterface;

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function get(): array;

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function getIndexList(): array;

    /**
     * @param FieldTypeInterface $field
     * @param string|null        $direction
     * @return BuilderInterface
     */
    public function sortBy(FieldTypeInterface $field, ?string $direction = 'asc'): BuilderInterface;

    /**
     * @param IndexInterface $index
     * @return string|false
     * @throws RedisException RedisException.
     */
    public function getDocument(IndexInterface $index): string|bool;

    /**
     * @param IndexInterface $index
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function dropIndex(IndexInterface $index): bool;

    /**
     * @param FieldTypeInterface $field
     * @param array              $values
     * @return BuilderInterface
     */
    public function whereInNumbers(FieldTypeInterface $field, array $values): BuilderInterface;

    /**
     * @param FieldTypeInterface $field
     * @param array              $values
     * @return BuilderInterface
     */
    public function whereInArrayText(FieldTypeInterface $field, array $values): BuilderInterface;

    /**
     * @param IndexInterface $index
     * @return boolean
     * @throws RedisException RedisException.
     */
    public function deleteDocument(IndexInterface $index): bool;

    /**
     * @return BuilderInterface
     */
    public function disableClear(): BuilderInterface;

    /**
     * @param FieldTypeInterface $field
     * @param string             $value
     * @return $this
     */
    public function whereTextFieldLike(FieldTypeInterface $field, string $value): BuilderInterface;

    /**
     * @param FieldTypeInterface $field
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function max(FieldTypeInterface $field): mixed;

    /**
     * @param FieldTypeInterface $field
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function min(FieldTypeInterface $field): mixed;

    /**
     * @return array
     * @throws RedisException RedisException.
     */
    public function getFirst(): array;
}
