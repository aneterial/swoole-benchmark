<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserServiceInterface;

final readonly class UsersController
{
    public function __construct(private UserServiceInterface $userService)
    {
    }

    /**
     * @return array{status: string}
     */
    public function index(string $name): array
    {
        return $this->userService->getUsers($name);
    }

    public function indexV2(string $name): array
    {
        return $this->userService->getUsersV2($name);
    }
}
