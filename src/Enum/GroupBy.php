<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Enum;

enum GroupBy: string
{
    case User = 'user';
    case Customer = 'customer';
    case Project = 'project';
    case Service = 'service';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
