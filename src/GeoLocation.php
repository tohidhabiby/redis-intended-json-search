<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch;

class GeoLocation
{
    /**
     * @param float $longitude
     * @param float $latitude
     */
    public function __construct(public float $longitude, public float $latitude)
    {
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%f %f', $this->longitude, $this->latitude);
    }
}
