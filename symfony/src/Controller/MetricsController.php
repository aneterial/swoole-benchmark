<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MetricsServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class MetricsController extends AbstractController
{
    public function __construct(private MetricsServiceInterface $metrics)
    {
    }

    #[Route('/metrics/{name}', name: 'metrics_index', methods: ['GET'])]
    public function index(string $name): JsonResponse
    {
        return $this->json($this->metrics->getStats($name));
    }
}
