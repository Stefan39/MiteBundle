<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Enum;

enum ActiveHourlyRate: string
{
    case HourlyRate = 'hourly_rate';
    case HourlyRatesPerService = 'hourly_rates_per_service';
}
