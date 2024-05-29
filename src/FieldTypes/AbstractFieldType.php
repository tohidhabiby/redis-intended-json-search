<?php

declare(strict_types=1);

namespace TohidHabiby\RedisIntendedJsonSearch\FieldTypes;

abstract class AbstractFieldType
{
    /** @var mixed */
    protected mixed $value;

    /**
     * @param string      $name
     * @param string|null $alias
     */
    public function __construct(protected string $name, protected ?string $alias = null)
    {
        if (! $this->alias) {
            $this->alias = $this->name;
        }
    }

    /**
     * @return string
     */
    abstract public function getType(): string;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }
}
