<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Exception;

/**
 * HTTP 422: mite rejected the request, e.g. missing attributes or a delete that requires archiving.
 */
final class ValidationException extends MiteException
{
    /**
     * @param list<string> $errors
     */
    public function __construct(string $message, int $code, private readonly array $errors = [])
    {
        parent::__construct($message, $code);
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
