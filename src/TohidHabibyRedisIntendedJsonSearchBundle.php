<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch;

use TohidHabiby\RedisIntendedJsonSearch\DependencyInjection\RedisIntendedJsonSearchExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TohidHabibyRedisIntendedJsonSearchBundle extends Bundle
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    /**
     * @return ExtensionInterface|null
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new RedisIntendedJsonSearchExtension();
    }
}
