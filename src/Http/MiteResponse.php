<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Http;

final readonly class MiteResponse
{
    /**
     * @param mixed                       $data    decoded JSON body, null for an empty body
     * @param array<string, list<string>> $headers lower-cased header names
     */
    public function __construct(
        public int $status,
        public mixed $data,
        public array $headers,
    ) {
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)][0] ?? null;
    }
}
