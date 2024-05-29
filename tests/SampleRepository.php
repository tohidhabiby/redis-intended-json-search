<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests;

use TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface;
use TohidHabiby\RedisIntendedJsonSearch\Repositories\AbstractRepository;

class SampleRepository extends AbstractRepository
{
    /**
     * @return IndexInterface
     */
    public function newInstance(): IndexInterface
    {
        return new SampleIndex();
    }
}
