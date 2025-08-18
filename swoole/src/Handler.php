<?php

declare(strict_types=1);

namespace App;

use Swoole\Http\Request;

final readonly class Handler
{
    public function __construct(
        private Metrics $metrics,
        private Users $users,
    ) {
    }

    public function metrics(array $vars, Request $request): string
    {
        return json_encode($this->metrics->getStats($vars['name'] ?? ''), JSON_UNESCAPED_UNICODE);
    }

    public function users(array $vars, Request $request): string
    {
        $results = $this->users->getUsers($vars['name']);

        $this->metrics->save('process', memory_get_usage());

        return json_encode($results, JSON_UNESCAPED_UNICODE);
    }

    public function sample(array $vars, Request $request): string
    {
        return json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
    }
}
