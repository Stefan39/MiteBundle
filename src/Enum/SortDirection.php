<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Enum;

enum SortDirection: string
{
    case Asc = 'asc';
    case Desc = 'desc';
}
