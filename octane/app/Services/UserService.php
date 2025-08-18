<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Metrics;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Octane\Facades\Octane;

final readonly class UserService implements UserServiceInterface
{
    public function __construct(private Metrics $metrics)
    {
    }

    public function getUsers(string $name): array
    {
        $result = Octane::concurrently([
            'data' => static fn (): Collection => DB::table('users')
                ->where('name', 'like', "%$name%")
                ->limit(100)
                ->get(),
            'total' => static fn (): int => DB::table('users')
                ->where('name', 'like', "%$name%")
                ->count(),
        ]);

        $this->metrics->save(Metrics::MEMORY_PROCESS, memory_get_usage());

        return $result;
    }
}
