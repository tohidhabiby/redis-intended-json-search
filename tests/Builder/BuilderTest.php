<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests\Builder;

use TohidHabiby\RedisIntendedJsonSearch\Builder\Builder;
use TohidHabiby\RedisIntendedJsonSearch\Builder\RedisConnection;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\NumericField;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\TextField;
use PHPUnit\Framework\MockObject\MockObject;
use TohidHabiby\RedisIntendedJsonSearch\Tests\SampleIndex;
use TohidHabiby\RedisIntendedJsonSearch\Tests\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class BuilderTest extends TestCase
{
    private Builder $builder;

    private RedisConnection&MockObject $connection;

    private SerializerInterface&MockObject $serializer;

    protected function setUp(): void
    {
        $this->connection = $this->getMockBuilder(RedisConnection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();
        $this->builder    = new Builder(
            $this->connection,
            $this->serializer,
        );
        $this->builder->disableClear();
    }

    public function testSelect(): void
    {
        $sampleIndex = new SampleIndex();
        $fields = [
            new NumericField('id', 'sampleId'),
            new TextField('name', 'sampleName'),
        ];
        $this->connection->expects($this->any())->method('run')->willReturn([]);
        $this->builder->searchOn($sampleIndex)->select($fields)->disableClear()->get();

        $this->assertEquals(['sampleId', 'sampleName'], $this->builder->select);
        $this->assertTrue(in_array('RETURN', $this->builder->arguments));
    }

    public function testCreateIndex(): void
    {
        $this->connection->expects($this->any())->method('run')->willReturn(true);
        $sampleIndex = new SampleIndex();
        $this->builder->createIndex($sampleIndex);

        $this->assertEquals(
            [
                'sample', 'ON',
                'JSON',
                'PREFIX',
                1,
                's',
                'SCHEMA',
                '$.id',
                'AS',
                'sampleId',
                'NUMERIC',
                'SORTABLE',
                '$.name',
                'AS',
                'sampleName',
                'TEXT',
                'SORTABLE',
                '$.children.*.id',
                'AS',
                'sampleIdChildLevel1',
                'NUMERIC',
                'SORTABLE',
                '$.children.*.name',
                'AS',
                'sampleNameChildLevel1',
                'TEXT',
                'SORTABLE',
            ],
            $this->builder->arguments
        );
        $this->assertEquals('FT.CREATE', $this->builder->command);
    }

    public function testSaveJsonDocument(): void
    {
        $this->connection->expects($this->any())->method('run')->willReturn(true);
        $sampleIndex       = new SampleIndex();
        $id                = $this->faker->randomNumber();
        $sampleIndex->id   = $id;
        $sampleIndex->name = $this->faker->name;
        $json              = json_encode($sampleIndex);
        $this->builder->saveJsonDocument($sampleIndex->getPrefix(), $sampleIndex->getId(), $json);

        $this->assertEquals(
            [
                's:' . $id,
                '$',
                $json
            ],
            $this->builder->arguments
        );
        $this->assertEquals('JSON.SET', $this->builder->command);
    }

    public function testGetDocument(): void
    {
        $this->connection->expects($this->any())->method('run')->willReturn('');
        $sampleIndex     = new SampleIndex();
        $id              = $this->faker->randomNumber();
        $sampleIndex->id = $id;
        $this->builder->getDocument($sampleIndex);

        $this->assertEquals(['s:' . $id], $this->builder->arguments);
        $this->assertEquals('JSON.GET', $this->builder->command);
    }

    public function testGetInfo(): void
    {
        $this->connection->expects($this->any())->method('run')->willReturn([]);
        $this->builder->getInfo(new SampleIndex());

        $this->assertEquals(
            ['sample'],
            $this->builder->arguments
        );
        $this->assertEquals('FT.INFO', $this->builder->command);
    }

    public function testSave(): void
    {
        $this->connection->expects($this->any())->method('run')->willReturn(true);
        $sampleIndex       = new SampleIndex();
        $id                = $this->faker->randomNumber();
        $sampleIndex->id   = $id;
        $sampleIndex->name = $this->faker->name;
        $json              = json_encode($sampleIndex);
        $this->serializer->expects($this->any())->method('serialize')->willReturn($json);
        $this->builder->save($sampleIndex);

        $this->assertEquals(
            [
                's:' . $id,
                '$',
                $json
            ],
            $this->builder->arguments
        );
        $this->assertEquals('JSON.SET', $this->builder->command);
    }

    public function testSearchOn(): void
    {
        $index = new SampleIndex();
        $this->builder->searchOn($index);

        $this->assertEquals($index, $this->builder->index);
        $this->assertEquals('FT.SEARCH', $this->builder->command);
        $this->assertEquals(['sample'], $this->builder->arguments);
    }

    public function testLimit(): void
    {
        $limit  = $this->faker->numberBetween(3, 100);
        $offset = $this->faker->numberBetween(3, 100);
        $this->builder->limit($limit, $offset);

        $this->assertEquals($limit, $this->builder->limit);
        $this->assertEquals($offset, $this->builder->offset);
    }

    public function testWhereNumberBetween(): void
    {
        $numericField = new NumericField('sample');
        $min          = $this->faker->numberBetween(0, 100);
        $max          = $this->faker->numberBetween($min, 1000);
        $this->builder->whereBetweenNumbers($numericField, $min, $max);

        $this->assertTrue(in_array('@sample:[' . $min . ',' . $max . ']', $this->builder->wheres));
    }

    public function testSortBy(): void
    {
        $direction = $this->faker->randomElement(['asc', 'desc']);
        $field     = new TextField('sample');
        $this->builder->sortBy($field, $direction);

        $this->assertEquals(['SORTBY', $field->getAlias(), $direction], $this->builder->sort);
        $this->assertEquals(1, $this->builder->firstKey);
        $this->assertEquals(3, $this->builder->secondKey);
    }

    public function testDropIndex(): void
    {
        $this->connection->expects($this->any())->method('run')->willReturn(true);
        $this->builder->dropIndex(new SampleIndex());

        $this->assertEquals('FT.DROP', $this->builder->command);
        $this->assertEquals(['sample'], $this->builder->arguments);
    }

    public function testGet(): void
    {
        $this->connection->expects($this->any())->method('run')->willReturn([]);
        $index = new SampleIndex();
        $field = new NumericField('id', 'sampleId');
        $min   = $this->faker->numberBetween(0, 1000);
        $max   = $this->faker->numberBetween($min, 10000);
        $limit = $this->faker->numberBetween(2, 10);

        $this->builder->searchOn($index)
            ->whereBetweenNumbers($field, $min, $max)
            ->sortBy($field)
            ->limit($limit)
            ->get();

        $this->assertEquals('FT.SEARCH', $this->builder->command);
        $this->assertEquals(
            [
                'sample',
                '(@sampleId:[' . $min . ',' . $max . '])',
                'SORTBY',
                'sampleId',
                'asc',
                'LIMIT',
                0,
                $limit
            ],
            $this->builder->arguments
        );
    }

    public function testWhereInNumbers(): void
    {
        $this->connection->expects($this->any())->method('run')->willReturn([]);
        $field = new NumericField('id', 'sampleId');
        $ids   = [5, 12];
        $this->builder->whereInNumbers($field, $ids);

        $this->assertEquals(
            ['@sampleId:[5,5]|@sampleId:[12,12]'],
            $this->builder->wheres
        );
    }

    public function testDeleteDocument(): void
    {
        $response = $this->faker->boolean();
        $this->connection->expects($this->any())->method('run')->willReturn((int)$response);
        $id            = $this->faker->numberBetween(1, 100);
        $index         = new SampleIndex();
        $index->id     = $id;
        $checkResponse = $this->builder->deleteDocument($index);

        $this->assertEquals($response, $checkResponse);
        $this->assertEquals(['s:' . $id], $this->builder->arguments);
        $this->assertEquals('JSON.DEL', $this->builder->command);
    }

    public function testMin(): void
    {
        $index = new SampleIndex();
        $this->builder->searchOn($index);
        $field = new NumericField('id', 'sampleId');
        $this->connection->expects($this->any())->method('run')->willReturn(null);
        $this->builder->min($field);

        $this->assertEquals('FT.AGGREGATE', $this->builder->command);
        $this->assertEquals(
            [
                'sample',
                '*',
                'GROUPBY',
                0,
                'REDUCE',
                'MIN',
                1,
                '@sampleId',
                'LIMIT',
                0,
                10000,
            ],
            $this->builder->arguments
        );
    }

    public function testMax(): void
    {
        $index = new SampleIndex();
        $this->builder->searchOn($index);
        $field = new NumericField('id', 'sampleId');
        $this->connection->expects($this->any())->method('run')->willReturn(null);
        $this->builder->max($field);

        $this->assertEquals('FT.AGGREGATE', $this->builder->command);
        $this->assertEquals(
            [
                'sample',
                '*',
                'GROUPBY',
                0,
                'REDUCE',
                'MAX',
                1,
                '@sampleId',
                'LIMIT',
                0,
                10000,
            ],
            $this->builder->arguments
        );
    }
}
