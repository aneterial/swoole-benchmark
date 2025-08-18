<?php

declare(strict_types=1);

namespace App\Service;

use App\Annotation\Metrics;
use Hyperf\Collection\Collection;
use Hyperf\DbConnection\Db;

use function Hyperf\Coroutine\parallel;

final readonly class UserService implements UserServiceInterface
{
    #[Metrics]
    public function getUsers(string $name): array
    {
        return parallel([
            'data' => static fn (): Collection => Db::connection()
                ->table('users')
                ->where('name', 'like', "%$name%")
                ->limit(100)
                ->get(),
            'total' => static fn (): int => Db::connection()
                ->table('users')
                ->where('name', 'like', "%$name%")
                ->count(),
        ]);
    }
}
