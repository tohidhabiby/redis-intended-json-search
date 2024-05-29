<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\FieldTypes;

use Attribute;

#[Attribute]
class NumericField extends AbstractFieldType implements FieldTypeInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'NUMERIC';
    }

    /**
     * @return string
     */
    public function getIndexDelimiter(): string
    {
        return '';
    }
}
