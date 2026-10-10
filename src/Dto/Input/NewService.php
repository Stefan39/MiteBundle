<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Input;

use Stejac\MiteBundle\Internal\Payload;

final readonly class NewService
{
    /**
     * @param int|null $hourlyRate cents
     */
    public function __construct(
        public string $name,
        public ?string $note = null,
        public ?int $hourlyRate = null,
        public ?bool $billable = null,
        public ?bool $archived = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Payload::compact([
            'name' => $this->name,
            'note' => $this->note,
            'hourly_rate' => $this->hourlyRate,
            'billable' => $this->billable,
            'archived' => $this->archived,
        ]);
    }
}
