<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\IndexField;
use TohidHabiby\RedisIntendedJsonSearch\Tests\SampleIndex;
use TohidHabiby\RedisIntendedJsonSearch\Tests\TestCase;

class RedisFieldTypesIndexFieldTest extends TestCase
{
    public function testShouldGetCorrectData(): void
    {
        $indexField = new IndexField(SampleIndex::class);

        $this->assertEquals('INDEX_FIELD', $indexField->getType());
        $this->assertEquals('sample', $indexField->getName());
        $this->assertEquals('sample', $indexField->getAlias());
        $this->assertEquals('.', $indexField->getIndexDelimiter());
    }
}
