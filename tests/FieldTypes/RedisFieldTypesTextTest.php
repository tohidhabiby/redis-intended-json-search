<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\TextField;
use TohidHabiby\RedisIntendedJsonSearch\Tests\TestCase;

class RedisFieldTypesTextTest extends TestCase
{
    public function testShouldGetCorrectData(): void
    {
        $name      = $this->faker->name;
        $alias     = $this->faker->name;
        $textField = new TextField($name, $alias);

        $this->assertEquals('TEXT', $textField->getType());
        $this->assertEquals($name, $textField->getName());
        $this->assertEquals($alias, $textField->getAlias());
        $this->assertEquals('', $textField->getIndexDelimiter());
    }
}
