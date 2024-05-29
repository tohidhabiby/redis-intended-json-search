<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\GeoField;
use TohidHabiby\RedisIntendedJsonSearch\Tests\TestCase;

class RedisFieldTypesGeoTest extends TestCase
{
    public function testShouldGetCorrectData(): void
    {
        $name     = $this->faker->name;
        $alias    = $this->faker->name;
        $geoField = new GeoField($name, $alias);

        $this->assertEquals('GEO', $geoField->getType());
        $this->assertEquals($name, $geoField->getName());
        $this->assertEquals($alias, $geoField->getAlias());
        $this->assertEquals('', $geoField->getIndexDelimiter());
    }
}
