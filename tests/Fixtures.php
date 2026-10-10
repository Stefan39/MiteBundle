<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests;

/**
 * Invented sample data mirroring the documented mite JSON shapes. Never put real data here.
 */
final class Fixtures
{
    public const CREATED_AT = '2026-01-02T10:00:00+01:00';
    public const UPDATED_AT = '2026-03-04T11:30:00+01:00';

    /**
     * @return array<string, mixed>
     */
    public static function account(): array
    {
        return ['id' => 1, 'name' => 'acme', 'title' => 'Acme Inc.', 'currency' => 'EUR', 'created_at' => self::CREATED_AT, 'updated_at' => self::UPDATED_AT];
    }

    /**
     * @return array<string, mixed>
     */
    public static function user(): array
    {
        return [
            'id' => 7, 'name' => 'Jane Doe', 'email' => 'jane@example.com', 'note' => '', 'archived' => false,
            'role' => 'admin', 'language' => 'en', 'created_at' => self::CREATED_AT, 'updated_at' => self::UPDATED_AT,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function bookmark(): array
    {
        return [
            'id' => 3, 'name' => 'Website this week', 'query' => 'project_id=11&at=this_week', 'type' => 'time_entries',
            'account_id' => 1, 'user_id' => null, 'created_at' => self::CREATED_AT, 'updated_at' => self::UPDATED_AT,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function service(): array
    {
        return [
            'id' => 21, 'name' => 'Development', 'note' => '', 'hourly_rate' => 9000, 'archived' => false, 'billable' => true,
            'created_at' => self::CREATED_AT, 'updated_at' => self::UPDATED_AT,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function customer(): array
    {
        return [
            'id' => 31, 'name' => 'Example Corp', 'note' => '', 'active_hourly_rate' => 'hourly_rates_per_service',
            'hourly_rate' => null, 'archived' => false,
            'hourly_rates_per_service' => [['service_id' => 21, 'hourly_rate' => 9500]],
            'created_at' => self::CREATED_AT, 'updated_at' => self::UPDATED_AT,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function project(): array
    {
        return [
            'id' => 11, 'name' => 'Website', 'note' => 'Relaunch', 'customer_id' => 31, 'customer_name' => 'Example Corp',
            'budget' => 6000, 'budget_type' => 'minutes', 'hourly_rate' => 8000, 'archived' => false,
            'active_hourly_rate' => 'hourly_rate', 'hourly_rates_per_service' => [],
            'created_at' => self::CREATED_AT, 'updated_at' => self::UPDATED_AT,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function timeEntry(): array
    {
        return [
            'id' => 101, 'minutes' => 90, 'date_at' => '2026-10-09', 'note' => 'Code review', 'billable' => true, 'locked' => false,
            'revenue' => 12000.0, 'hourly_rate' => 8000, 'user_id' => 7, 'user_name' => 'Jane Doe',
            'project_id' => 11, 'project_name' => 'Website', 'customer_id' => 31, 'customer_name' => 'Example Corp',
            'service_id' => 21, 'service_name' => 'Development', 'created_at' => self::CREATED_AT, 'updated_at' => self::UPDATED_AT,
        ];
    }
}
