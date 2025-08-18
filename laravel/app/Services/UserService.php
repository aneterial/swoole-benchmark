<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Utils\Metrics;

final readonly class UserService implements UserServiceInterface
{
    public function __construct(private Metrics $metrics)
    {
    }

    public function getUsers(string $name): array
    {
        $result = [
            'data' => User::query()
                ->where('name', 'like', "%$name%")
                ->limit(100)
                ->get(),
            'total' => User::query()
                ->where('name', 'like', "%$name%")
                ->count(),
        ];

        $this->metrics->save(Metrics::MEMORY_PROCESS, memory_get_usage());

        return $result;
    }
}
