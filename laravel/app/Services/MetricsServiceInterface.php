<?php

declare(strict_types=1);

namespace App\Services;

interface MetricsServiceInterface
{
    public function getStats(string $key): array;
}
