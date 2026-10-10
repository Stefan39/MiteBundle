<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

final readonly class ChangeSet
{
    /**
     * @param list<Change>       $changes may contain duplicates; mite over-delivers by design
     * @param ChangesCursor|null $next    cursor for the next fetch
     */
    public function __construct(
        public array $changes,
        public ?ChangesCursor $next,
    ) {
    }
}
