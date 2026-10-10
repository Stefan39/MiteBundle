<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Internal;

use PHPUnit\Framework\TestCase;
use Stejac\MiteBundle\Enum\BudgetType;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Internal\Data;

final class DataTest extends TestCase
{
    public function testUnwrapReturnsInnerObject(): void
    {
        self::assertSame(['id' => 1], Data::unwrap(['service' => ['id' => 1]], 'service'));
    }

    public function testUnwrapListReturnsInnerObjects(): void
    {
        self::assertSame([['id' => 1], ['id' => 2]], Data::unwrapList([['service' => ['id' => 1]], ['service' => ['id' => 2]]], 'service'));
    }

    public function testUnwrapListOfEmptyArray(): void
    {
        self::assertSame([], Data::unwrapList([], 'service'));
    }

    public function testUnwrapRejectsNull(): void
    {
        $this->expectException(InvalidResponseException::class);
        Data::unwrap(null, 'service');
    }

    public function testUnwrapRejectsWrongKey(): void
    {
        $this->expectException(InvalidResponseException::class);
        Data::unwrap(['project' => ['id' => 1]], 'service');
    }

    public function testObjectAcceptsEmptyObject(): void
    {
        self::assertSame([], Data::object([], 'tracker'));
    }

    public function testScalars(): void
    {
        $data = ['i' => 3, 's' => 'x', 'b' => false, 'f' => 12, 'n' => null];

        self::assertSame(3, Data::int($data, 'i'));
        self::assertNull(Data::nullableInt($data, 'n'));
        self::assertSame('x', Data::string($data, 's'));
        self::assertNull(Data::nullableString($data, 'missing'));
        self::assertSame('', Data::text($data, 'n'));
        self::assertFalse(Data::bool($data, 'b'));
        self::assertSame(12.0, Data::nullableFloat($data, 'f'));
        self::assertNull(Data::nullableFloat($data, 'n'));
    }

    public function testWrongTypeNamesTheField(): void
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('"id"');
        Data::int(['id' => '3'], 'id');
    }

    public function testDates(): void
    {
        $data = ['at' => '2026-10-09T08:15:00+02:00', 'day' => '2026-10-09', 'none' => null];

        self::assertSame('2026-10-09T08:15:00+02:00', Data::dateTime($data, 'at')->format(\DATE_ATOM));
        self::assertSame('2026-10-09 00:00:00', Data::date($data, 'day')->format('Y-m-d H:i:s'));
        self::assertNull(Data::nullableDate($data, 'none'));
    }

    public function testInvalidDateRaises(): void
    {
        $this->expectException(InvalidResponseException::class);
        Data::date(['day' => '09.10.2026'], 'day');
    }

    public function testInvalidDateTimeRaises(): void
    {
        $this->expectException(InvalidResponseException::class);
        Data::dateTime(['at' => 'not a date'], 'at');
    }

    public function testEnum(): void
    {
        self::assertSame(BudgetType::CentsPerMonth, Data::enum(['t' => 'cents_per_month'], 't', BudgetType::class));
        self::assertNull(Data::nullableEnum(['t' => null], 't', BudgetType::class));
    }

    public function testUnknownEnumValueRaisesInvalidResponseNotValueError(): void
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('"budget_type"');
        Data::enum(['budget_type' => 'hours'], 'budget_type', BudgetType::class);
    }
}
