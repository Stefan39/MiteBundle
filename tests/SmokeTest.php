<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests;

use PHPUnit\Framework\TestCase;
use Stejac\MiteBundle\MiteBundle;

final class SmokeTest extends TestCase
{
    public function testBundleClassIsLoadable(): void
    {
        self::assertInstanceOf(MiteBundle::class, new MiteBundle());
    }
}
