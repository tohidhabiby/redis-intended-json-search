<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;

interface IndexFieldInterface extends FieldTypeInterface
{
    /**
     * @return IndexInterface
     */
    public function getIndex(): IndexInterface;
}
