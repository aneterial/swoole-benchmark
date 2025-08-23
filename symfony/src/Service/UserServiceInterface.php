<?php

declare(strict_types=1);

namespace App\Service;

interface UserServiceInterface
{
    public function getUsers(string $name): array;

    public function getUsersV2(string $name): array;
}
