<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Filter;

use TohidHabiby\RedisIntendedJsonSearch\Repositories\RepositoryInterface;

interface FilterInterface
{
    /**
     * @param RepositoryInterface $repository
     * @return FilterInterface
     */
    public function setRepository(RepositoryInterface $repository): FilterInterface;

    /**
     * @return RepositoryInterface
     */
    public function apply(): RepositoryInterface;
}
