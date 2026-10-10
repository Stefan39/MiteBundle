<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Dto;

use PHPUnit\Framework\TestCase;
use Stejac\MiteBundle\Dto\HourlyRatePerService;

final class HourlyRatePerServiceTest extends TestCase
{
    public function testListFromPlainAndWrappedItems(): void
    {
        $rates = HourlyRatePerService::listFromArray([
            'hourly_rates_per_service' => [
                ['service_id' => 21, 'hourly_rate' => 9500],
                ['hourly_rate_per_service' => ['service_id' => 22, 'hourly_rate' => 7000]],
            ],
        ], 'hourly_rates_per_service');

        self::assertCount(2, $rates);
        self::assertSame(21, $rates[0]->serviceId);
        self::assertSame(7000, $rates[1]->hourlyRate);
        self::assertSame(['service_id' => 21, 'hourly_rate' => 9500], $rates[0]->toArray());
    }

    public function testMissingListIsEmpty(): void
    {
        self::assertSame([], HourlyRatePerService::listFromArray([], 'hourly_rates_per_service'));
    }
}
