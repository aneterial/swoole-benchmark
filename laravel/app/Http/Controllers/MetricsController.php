<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MetricsServiceInterface;

final readonly class MetricsController
{
    public function __construct(private MetricsServiceInterface $metricsService)
    {
    }

    public function index(string $key): array
    {
        return $this->metricsService->getStats($key);
    }
}
