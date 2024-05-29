<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\Tests;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase as MainTestCase;

class TestCase extends MainTestCase
{
    protected Generator $faker;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->faker = Factory::create();
    }
}
