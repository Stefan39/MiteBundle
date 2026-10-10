<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Exception;

/**
 * mite answered with a body that does not match the documented shape.
 */
final class InvalidResponseException extends MiteException
{
}
