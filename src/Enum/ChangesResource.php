<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Enum;

enum ChangesResource: string
{
    case Projects = 'projects';
    case Customers = 'customers';
    case Services = 'services';
    case TimeEntries = 'time_entries';
}
