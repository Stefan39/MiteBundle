<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Exception;

/**
 * HTTP 401: the API key is missing or wrong.
 */
final class AuthenticationException extends MiteException
{
}
