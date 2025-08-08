<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final readonly class RequestLogMiddleware implements MiddlewareInterface
{
    #[Inject]
    private LoggerInterface $logger;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = Uuid::uuid7()->toString();

        $this->logger->info('Start request', [
            'request_id' => $requestId,
            'request_uri' => $request->getUri()->getPath(),
        ]);

        $response = $handler->handle($request);

        $this->logger->info('End request', [
            'request_id' => $requestId,
            'request_uri' => $request->getUri()->getPath(),
        ]);

        return $response;
    }
}
