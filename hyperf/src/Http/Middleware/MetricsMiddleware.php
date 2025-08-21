<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Utils\Metrics;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class MetricsMiddleware implements MiddlewareInterface
{
    #[Inject]
    private Metrics $metrics;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->metrics->save(Metrics::MEMORY_START, memory_get_usage(true));

        $response = $handler->handle($request);

        $this->metrics->save(Metrics::MEMORY_END, memory_get_usage(true));

        return $response;
    }
}
