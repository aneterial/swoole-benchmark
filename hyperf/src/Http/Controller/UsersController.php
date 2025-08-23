<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Service\UserServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

final readonly class UsersController
{
    #[Inject(lazy: true)]
    private UserServiceInterface $userService;

    public function index(string $name, ResponseInterface $response): PsrResponseInterface
    {
        return $response->json($this->userService->getUsers($name));
    }

    public function indexV2(string $name, ResponseInterface $response): PsrResponseInterface
    {
        return $response->json($this->userService->getUsersV2($name));
    }
}
