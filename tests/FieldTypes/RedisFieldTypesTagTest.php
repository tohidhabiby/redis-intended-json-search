<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\TagField;
use TohidHabiby\RedisIntendedJsonSearch\Tests\TestCase;

class RedisFieldTypesTagTest extends TestCase
{
    public function testShouldGetCorrectData(): void
    {
        $name     = $this->faker->name;
        $alias    = $this->faker->name;
        $tagField = new TagField($name, $alias);

        $this->assertEquals('TAG', $tagField->getType());
        $this->assertEquals($name, $tagField->getName());
        $this->assertEquals($alias, $tagField->getAlias());
        $this->assertEquals('', $tagField->getIndexDelimiter());
    }
}
