<?php

declare(strict_types=1);

namespace App\Service;

use App\Annotation\Metrics;
use Hyperf\Collection\Collection;
use Hyperf\DbConnection\Db;
use Ramsey\Uuid\Uuid;

use function Hyperf\Coroutine\parallel;

final readonly class UserService implements UserServiceInterface
{
    #[Metrics]
    public function getUsers(string $name): array
    {
        return parallel([
            'data' => static fn (): array => Db::connection()
                ->table('users')
                ->where('name', 'like', "%$name%")
                ->limit(100)
                ->get()
                ->all(),
            'total' => static fn (): int => Db::connection()
                ->table('users')
                ->where('name', 'like', "%$name%")
                ->count(),
        ]);
    }

    #[Metrics]
    public function getUsersV2(string $name): array
    {
        $data = parallel([
            'data' => static fn (): array => Db::connection()
                ->table('users')
                ->where('name', 'like', "%$name%")
                ->limit(100)
                ->get()
                ->all(),
            'total' => static fn (): int => Db::connection()
                ->table('users')
                ->where('name', 'like', "%$name%")
                ->count(),
        ]);

        $uuids = array_map(
            static fn (): string => Uuid::uuid7()->toString(),
            range(0, 1000)
        );

        return [
            'data' => array_combine(
                array_slice($uuids, 0, count($data['data'])),
                $data['data']
            ),
            'total' => $data['total'],
        ];
    }
}
