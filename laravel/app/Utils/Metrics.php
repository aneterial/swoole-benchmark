<?php

declare(strict_types=1);

namespace App\Utils;

use Illuminate\Support\Facades\Redis;

final readonly class Metrics
{
    public const string MEMORY_START = 'start';
    public const string MEMORY_END = 'end';
    public const string MEMORY_PROCESS = 'process';

    public function save(string $key, int $value): void
    {
        Redis::rPush($this->normalizeKey($key), $value);
    }

    public function get(string $key): array
    {
        return array_map('intval', Redis::lRange($this->normalizeKey($key), 0, -1));
    }

    private function normalizeKey(string $key): string
    {
        return "laravel:memory:{$key}";
    }
}
