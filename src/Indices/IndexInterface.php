<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Indices;

use Symfony\Component\Serializer\Annotation\Ignore;

interface IndexInterface
{
    /**
     * @Ignore()
     * @return string
     */
    public function getIndexName(): string;

    /**
     * @Ignore()
     * @return string
     */
    public function getAlias(): string;

    /**
     * @Ignore()
     * @return string
     */
    public function getPrefix(): string;

    /**
     * @return integer|null
     */
    public function getId(): null|int;
}
