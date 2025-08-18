<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Utils\Metrics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class MetricsMiddleware
{
    public function __construct(private Metrics $metrics)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->metrics->save(Metrics::MEMORY_START, memory_get_usage());

        $response = $next($request);

        $this->metrics->save(Metrics::MEMORY_END, memory_get_usage());

        return $response;
    }
}
