<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;

abstract class AbstractIndexFieldType extends AbstractFieldType implements IndexFieldInterface
{
    /**
     * @param string      $className
     * @param string|null $name
     * @param string|null $alias
     */
    public function __construct(string $className, ?string $name = null, ?string $alias = null)
    {
        $this->index = new $className();
        $name        = $name ?? $this->index->getIndexName();
        $alias       = $alias ?? $this->index->getAlias();
        parent::__construct($name, $alias);
    }

    /** @var IndexInterface */
    protected IndexInterface $index;

    /**
     * @return IndexInterface
     */
    public function getIndex(): IndexInterface
    {
        return $this->index;
    }
}
