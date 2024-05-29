<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests\Repository;

use TohidHabiby\RedisIntendedJsonSearch\Builder\Builder;
use TohidHabiby\RedisIntendedJsonSearch\Builder\BuilderInterface;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\FieldTypeInterface;
use TohidHabiby\RedisIntendedJsonSearch\FieldTypes\TextField;
use TohidHabiby\RedisIntendedJsonSearch\Repositories\AbstractRepository;
use TohidHabiby\RedisIntendedJsonSearch\Tests\SampleIndex;
use TohidHabiby\RedisIntendedJsonSearch\Tests\SampleRepository;
use TohidHabiby\RedisIntendedJsonSearch\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AbstractRepositoryTest extends TestCase
{
    private AbstractRepository $repository;

    private BuilderInterface&MockObject $builder;

    protected function setUp(): void
    {
        $this->builder    = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'execute', 'deleteDocument'])
            ->getMock();
        $this->repository = new SampleRepository($this->builder);
    }

    public function testDeleteById(): void
    {
        $response = $this->faker->boolean();
        $this->builder->expects($this->any())->method('deleteDocument')->willReturn($response);
        $id            = $this->faker->numberBetween(1, 100);

        $this->assertEquals($response, $this->repository->deleteById($id));
    }

    public function testNewInstance(): void
    {
        $this->assertEquals(
            new SampleIndex(),
            $this->repository->newInstance()
        );
    }

    public function testFill(): void
    {
        $id              = $this->faker->randomNumber();
        $name            = $this->faker->name;
        $index           = new SampleIndex();
        $index->name     = $name;
        $index->id       = $id;
        $index->children = null;
        $data            = ['id' => $id, 'name' => $name];

        $this->assertEquals($index, $this->repository->fill($data)->getModel());
    }

    public function testGetFieldByName(): void
    {
        $this->assertEquals(
            new TextField('name', 'sampleName'),
            $this->repository->getFieldByName('name')
        );
        $this->assertTrue($this->repository->getFieldByName('name') instanceof FieldTypeInterface);
        $this->expectExceptionMessage('The field is not exists!');
        $this->repository->getFieldByName('test');
    }

    public function testPagination(): void
    {
        $page = $this->faker->numberBetween(0, 50);
        $perPage = $this->faker->numberBetween(0, 50);
        $this->builder->expects($this->any())->method('get')->willReturn([]);
        $this->repository->paginate($page, $perPage);

        $this->assertEquals($page, $this->builder->offset);
        $this->assertEquals($perPage, $this->builder->limit);
    }
}
