<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class SampleController extends AbstractController
{
    #[Route('/sample', name: 'sample_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['status' => 'ok']);
    }
}
