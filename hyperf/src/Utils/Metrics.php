<?php

declare(strict_types=1);

namespace App\Utils;

use Hyperf\Redis\Redis;

final readonly class Metrics
{
    public const string MEMORY_START = 'start';
    public const string MEMORY_END = 'end';
    public const string MEMORY_PROCESS = 'process';

    public function __construct(private readonly Redis $redis)
    {
    }

    public function save(string $key, int $value): void
    {
        $this->redis->rPush($this->normalizeKey($key), $value);
    }

    public function get(string $key): array
    {
        return array_map('intval', $this->redis->lRange($this->normalizeKey($key), 0, -1));
    }

    private function normalizeKey(string $key): string
    {
        return "hyperf:memory:$key";
    }
}
