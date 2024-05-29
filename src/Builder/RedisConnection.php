<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Builder;

use Redis;
use RedisException;

class RedisConnection
{
    /**
     * @param Redis        $redis
     * @param string       $host
     * @param integer|null $port
     * @param integer|null $timeout
     * @param string|null  $persistentId
     * @param integer|null $retryInterval
     * @param integer|null $readTimeout
     * @throws RedisException RedisException.
     */
    public function __construct(
        public Redis $redis,
        string $host,
        ?int $port = 6379,
        ?int $timeout = 0,
        ?string $persistentId = null,
        ?int $retryInterval = 0,
        ?int $readTimeout = 0,
    )
    {
        $this->redis->connect($host, $port, $timeout, $persistentId, $readTimeout, $retryInterval);
    }

    /**
     * @param string $command
     * @param array  $attributes
     * @return mixed
     * @throws RedisException RedisException.
     */
    public function run(string $command, array $attributes): mixed
    {
        return $this->redis->rawCommand($command, ...$attributes);
    }
}
