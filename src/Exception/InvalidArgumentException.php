<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Exception;

/**
 * A value passed to the bundle is invalid (configuration, filter or input DTO).
 */
final class InvalidArgumentException extends \InvalidArgumentException implements MiteExceptionInterface
{
}
