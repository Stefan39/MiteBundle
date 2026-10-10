<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Live;

use PHPUnit\Framework\TestCase;
use Stejac\MiteBundle\Http\MiteClient;

abstract class LiveTestCase extends TestCase
{
    protected static function env(string $name): ?string
    {
        $value = getenv($name);
        if (\is_string($value) && '' !== $value) {
            return $value;
        }

        $file = \dirname(__DIR__, 2).'/.env.local';
        if (!is_file($file)) {
            return null;
        }
        foreach (file($file, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (str_starts_with($line, $name.'=')) {
                return trim(substr($line, \strlen($name) + 1)) ?: null;
            }
        }

        return null;
    }

    protected function liveClient(): MiteClient
    {
        $domain = self::env('MITE_DOMAIN');
        $key = self::env('MITE_API_KEY');
        if (null === $domain || null === $key) {
            self::markTestSkipped('Set MITE_DOMAIN and MITE_API_KEY (environment or .env.local) to run live tests.');
        }

        return new MiteClient($domain, $key, 'stejac/mite-bundle live tests (https://github.com/Stefan39/MiteBundle)');
    }
}
