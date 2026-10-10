<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Exception;

/**
 * Base class for errors returned by mite or raised while talking to it.
 */
class MiteException extends \RuntimeException implements MiteExceptionInterface
{
}
