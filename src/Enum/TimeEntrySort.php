<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Enum;

enum TimeEntrySort: string
{
    case Date = 'date';
    case User = 'user';
    case Customer = 'customer';
    case Project = 'project';
    case Service = 'service';
    case Note = 'note';
    case Minutes = 'minutes';
    case Revenue = 'revenue';
}
