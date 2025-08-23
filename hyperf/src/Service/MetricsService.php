<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Metrics;
use Hyperf\Di\Annotation\Inject;

final readonly class MetricsService implements MetricsServiceInterface
{
    #[Inject]
    private Metrics $metrics;

    public function getStats(string $key): array
    {
        $metrics = $this->metrics->get($key);
        if (empty($metrics)) {
            return [
                'raw' => [
                    'max' => 0,
                    'min' => 0,
                    'avg' => 0,
                    'p95' => 0,
                ],
                'formatted' => [
                    'max' => $this->formatBytes(0),
                    'min' => $this->formatBytes(0),
                    'avg' => $this->formatBytes(0),
                    'p95' => $this->formatBytes(0),
                ],
                'values' => [],
                'count' => 0,
            ];
        }

        sort($metrics);

        $count = count($metrics);

        $max = max($metrics);
        $min = min($metrics);
        $avg = round(array_sum($metrics) / $count, 2);
        $p95 = $metrics[(int) ceil($count * 0.95) - 1] ?? 0;

        return [
            'raw' => [
                'max' => $max,
                'min' => $min,
                'avg' => $avg,
                'p95' => $p95,
            ],
            'formatted' => [
                'max' => $this->formatBytes($max),
                'min' => $this->formatBytes($min),
                'avg' => $this->formatBytes($avg),
                'p95' => $this->formatBytes($p95),
            ],
            'values' => array_slice($metrics, -50),
            'count' => $count,
        ];
    }

    private function formatBytes(float $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
