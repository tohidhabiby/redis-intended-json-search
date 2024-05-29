<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Indices;

use Attribute;

#[Attribute]
class Index
{
    /**
     * @param string $className
     */
    public function __construct(public string $className)
    {
    }

    /**
     * @return string
     */
    public function getRepository(): string
    {
        return $this->className;
    }
}
