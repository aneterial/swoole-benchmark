<?php

declare(strict_types=1);

namespace App\Utils;

final class Metrics
{
    public const string MEMORY_START = 'start';
    public const string MEMORY_END = 'end';
    public const string MEMORY_PROCESS = 'process';

    private array $metrics = [];

    public function __construct(private readonly int $limit = 500)
    {
    }

    public function save(string $key, int $value): void
    {
        $this->metrics[$key][] = $value;
        if (count($this->metrics[$key]) > $this->limit) {
            array_shift($this->metrics[$key]);
        }
    }

    public function get(string $key): array
    {
        return $this->metrics[$key] ?? [];
    }
}
