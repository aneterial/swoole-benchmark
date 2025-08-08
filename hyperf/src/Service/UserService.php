<?php

declare(strict_types=1);

namespace App\Service;

use App\Annotation\Metrics;
use App\Model\User;
use Hyperf\Database\Model\Collection;

use function Hyperf\Coroutine\parallel;

final readonly class UserService implements UserServiceInterface
{
    #[Metrics]
    public function getUsers(string $name): array
    {
        return parallel([
            'data' => static fn (): Collection => User::query()
                ->where('name', 'like', "%$name%")
                ->limit(100)
                ->get(),
            'total' => static fn (): int => User::query()
                ->where('name', 'like', "%$name%")
                ->count(),
        ]);
    }
}
