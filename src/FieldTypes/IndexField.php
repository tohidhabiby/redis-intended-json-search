<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;
use Attribute;

#[Attribute]
class IndexField extends AbstractIndexFieldType implements IndexFieldInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'INDEX_FIELD';
    }

    /**
     * @return string
     */
    public function getIndexDelimiter(): string
    {
        return '.';
    }
}
