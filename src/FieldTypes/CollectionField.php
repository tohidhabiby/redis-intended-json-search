<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\Indices\Collection;
use Attribute;

#[Attribute]
class CollectionField extends AbstractIndexFieldType implements IndexFieldInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'COLLECTION_FIELD';
    }

    /**
     * @return string
     */
    public function getIndexDelimiter(): string
    {
        return '.*.';
    }
}
