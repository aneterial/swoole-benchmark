<?php

declare(strict_types=1);

namespace App\Service;

interface MetricsServiceInterface
{
    public function getStats(string $key): array;
}
