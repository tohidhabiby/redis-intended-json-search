<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests\FieldTypes;

use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\CollectionField;
use TohidHabiby\RedisIntendedJsonSearch\Indices\Collection;
use TohidHabiby\RedisIntendedJsonSearch\Tests\SampleIndex;
use TohidHabiby\RedisIntendedJsonSearch\Tests\TestCase;

class RedisFieldTypesCollectionFieldTest extends TestCase
{
    public function testShouldGetCorrectData(): void
    {
        $sampleIndex       = new SampleIndex();
        $sampleIndex->id   = $this->faker->randomNumber();
        $sampleIndex->name = $this->faker->name;
        $collection        = new Collection();
        $collection->add($sampleIndex);
        $indexField = new CollectionField(SampleIndex::class);

        $this->assertEquals('COLLECTION_FIELD', $indexField->getType());
        $this->assertEquals('sample', $indexField->getName());
        $this->assertEquals('sample', $indexField->getAlias());
        $this->assertEquals('.*.', $indexField->getIndexDelimiter());
    }
}
