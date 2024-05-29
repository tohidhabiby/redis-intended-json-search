<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RedisIntendedJsonSearchExtension extends Extension
{
    /**
     * @param array            $config
     * @param ContainerBuilder $builder
     * @return void
     * @throws Exception Exception.
     */
    // phpcs:ignore
    public function load(array $config, ContainerBuilder $builder): void
    {
        $loader = new YamlFileLoader($builder, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }
}
