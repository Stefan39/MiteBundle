<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Input;

/**
 * Immutable partial update: only fields set through a with*() method are sent, including explicit nulls.
 */
abstract class AbstractUpdate
{
    /** @var array<string, mixed> */
    private array $fields = [];

    final public function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->fields;
    }

    public function isEmpty(): bool
    {
        return [] === $this->fields;
    }

    protected function with(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->fields[$key] = $value;

        return $clone;
    }
}
