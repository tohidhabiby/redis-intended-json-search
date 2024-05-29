<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\FieldTypes;

use Attribute;

#[Attribute]
class TagField extends AbstractFieldType implements FieldTypeInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'TAG';
    }

    /**
     * @return string
     */
    public function getIndexDelimiter(): string
    {
        return '';
    }
}
