<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UsersController extends AbstractController
{
    public function __construct(private UserServiceInterface $userService)
    {
    }

    #[Route('/users/{name}', name: 'users_index', methods: ['GET'])]
    public function index(Request $request, string $name): JsonResponse
    {
        $result = $this->userService->getUsers($name);

        return $this->json($result);
    }

    #[Route('/v2/users/{name}', name: 'users_index_v2', methods: ['GET'])]
    public function indexV2(Request $request, string $name): JsonResponse
    {
        $result = $this->userService->getUsersV2($name);

        return $this->json($result);
    }
}
