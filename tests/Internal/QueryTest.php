<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Internal;

use PHPUnit\Framework\TestCase;
use Stejac\MiteBundle\Dto\Filter\NameFilter;
use Stejac\MiteBundle\Enum\Period;
use Stejac\MiteBundle\Exception\InvalidArgumentException;
use Stejac\MiteBundle\Internal\Query;

final class QueryTest extends TestCase
{
    public function testCompactDropsNullAndEmptyAndStringifies(): void
    {
        self::assertSame(
            ['a' => 'x', 'b' => '5', 'c' => 'true', 'd' => 'false', 'f' => ['n1', 'n2']],
            Query::compact(['a' => 'x', 'b' => 5, 'c' => true, 'd' => false, 'e' => null, 'f' => ['n1', 'n2'], 'g' => []]),
        );
    }

    public function testIds(): void
    {
        self::assertSame('1,2,3', Query::ids([1, 2, 3]));
        self::assertNull(Query::ids([]));
    }

    public function testDate(): void
    {
        self::assertSame('this_week', Query::date(Period::ThisWeek));
        self::assertSame('2026-10-09', Query::date(new \DateTimeImmutable('2026-10-09 14:00')));
        self::assertNull(Query::date(null));
    }

    public function testNameFilter(): void
    {
        self::assertSame(['name' => 'web', 'limit' => '10', 'page' => '2'], (new NameFilter('web', 10, 2))->toQuery());
        self::assertSame([], (new NameFilter())->toQuery());
    }

    public function testZeroLimitIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new NameFilter(limit: 0);
    }

    public function testNegativePageIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new NameFilter(page: -1);
    }
}
