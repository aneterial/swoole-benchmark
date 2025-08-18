<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final readonly class RequestLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = Uuid::uuid7()->toString();

        Log::info('Start request', [
            'request_id' => $requestId,
            'request_uri' => $request->getPathInfo(),
        ]);

        $response = $next($request);

        Log::info('End request', [
            'request_id' => $requestId,
            'request_uri' => $request->getPathInfo(),
        ]);

        return $response;
    }

}
