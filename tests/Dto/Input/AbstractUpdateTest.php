<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Dto\Input;

use PHPUnit\Framework\TestCase;
use Stejac\MiteBundle\Dto\Input\AbstractUpdate;

final class AbstractUpdateTest extends TestCase
{
    public function testOnlySetFieldsAreSentAndNullIsKept(): void
    {
        $update = new class extends AbstractUpdate {
            public function withName(string $name): static
            {
                return $this->with('name', $name);
            }

            public function withRate(?int $rate): static
            {
                return $this->with('hourly_rate', $rate);
            }
        };

        $changed = $update->withName('New')->withRate(null);

        self::assertTrue($update->isEmpty());
        self::assertSame([], $update->toArray());
        self::assertSame(['name' => 'New', 'hourly_rate' => null], $changed->toArray());
    }
}
