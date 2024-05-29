<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests;

use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\CollectionField;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\NumericField;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\TextField;
use TohidHabiby\RedisIntendedJsonSearch\Indices\Collection;
use TohidHabiby\RedisIntendedJsonSearch\Indices\Index;
use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;

#[Index(SampleRepository::class)]
class SampleIndex implements IndexInterface
{
    #[NumericField(name: 'id', alias: 'sampleId')]
    public int|null $id;

    #[TextField(name: 'name', alias: 'sampleName')]
    public string|null $name;

    #[CollectionField(self::class, 'children', 'children')]
    public Collection|null $children;

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return 'sample';
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'sample';
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return 's';
    }

    /**
     * @return int|null
     */
    public function getId(): null|int
    {
        return $this->id;
    }
}
