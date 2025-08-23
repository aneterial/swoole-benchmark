<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Metrics;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Octane\Facades\Octane;
use Ramsey\Uuid\Uuid;

final readonly class UserService implements UserServiceInterface
{
    public function __construct(private Metrics $metrics)
    {
    }

    public function getUsers(string $name): array
    {
        $result = Octane::concurrently([
            'data' => static fn (): array => DB::table('users')
                ->where('name', 'like', "%$name%")
                ->limit(100)
                ->get()
                ->all(),
            'total' => static fn (): int => DB::table('users')
                ->where('name', 'like', "%$name%")
                ->count()
        ]);

        $this->metrics->save(Metrics::MEMORY_PROCESS, memory_get_usage(true));

        return $result;
    }

    public function getUsersV2(string $name): array
    {
        $result = Octane::concurrently([
            'data' => static fn (): array => DB::table('users')
                ->where('name', 'like', "%$name%")
                ->limit(100)
                ->get()
                ->all(),
            'total' => static fn (): int => DB::table('users')
                ->where('name', 'like', "%$name%")
                ->count(),
            'uuids' => static fn (): array => array_map(
                static fn (): string => Uuid::uuid7()->toString(),
                range(0, 1000)
            ),
        ]);

        $this->metrics->save(Metrics::MEMORY_PROCESS, memory_get_usage(true));

        return [
            'data' => array_combine(
                array_slice($result['uuids'], 0, count($result['data'])),
                $result['data']
            ),
            'total' => $result['total'],
        ];
    }
}
