<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\FieldTypes;

use Attribute;

#[Attribute]
class TextField extends AbstractFieldType implements FieldTypeInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'TEXT';
    }

    /**
     * @return string
     */
    public function getIndexDelimiter(): string
    {
        return '';
    }
}
