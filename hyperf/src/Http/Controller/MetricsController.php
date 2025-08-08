<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Service\MetricsServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

final readonly class MetricsController
{
    #[Inject]
    private MetricsServiceInterface $metricsService;

    public function index(string $name, ResponseInterface $response): PsrResponseInterface
    {
        return $response->json($this->metricsService->getStats($name));
    }
}
