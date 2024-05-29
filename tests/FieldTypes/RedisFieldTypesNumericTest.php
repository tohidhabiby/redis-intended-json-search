<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\NumericField;
use TohidHabiby\RedisIntendedJsonSearch\Tests\TestCase;

class RedisFieldTypesNumericTest extends TestCase
{
    public function testShouldGetCorrectData(): void
    {
        $name      = $this->faker->name;
        $alias     = $this->faker->name;
        $textField = new NumericField($name, $alias);

        $this->assertEquals('NUMERIC', $textField->getType());
        $this->assertEquals($name, $textField->getName());
        $this->assertEquals($alias, $textField->getAlias());
        $this->assertEquals('', $textField->getIndexDelimiter());
    }
}
