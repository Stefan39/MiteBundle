<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Enum;

enum BudgetType: string
{
    case Minutes = 'minutes';
    case MinutesPerMonth = 'minutes_per_month';
    case Cents = 'cents';
    case CentsPerMonth = 'cents_per_month';
}
