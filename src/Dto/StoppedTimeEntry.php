<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

final readonly class StoppedTimeEntry
{
    public function __construct(
        public int $id,
        public int $minutes,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(Data::int($data, 'id'), Data::int($data, 'minutes'));
    }
}
