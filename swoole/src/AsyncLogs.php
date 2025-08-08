<?php

declare(strict_types=1);

namespace App;

final readonly class AsyncLogs
{
    public static function info(string $format, ...$args): void
    {
        go(static function() use ($format, $args): void {
            printf($format, ...$args);
        });
    }
}
