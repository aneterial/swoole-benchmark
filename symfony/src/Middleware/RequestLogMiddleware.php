<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RequestLogMiddleware implements EventSubscriberInterface
{
    private ?string $requestId = null;

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 32],
            KernelEvents::RESPONSE => ['onKernelResponse', -9999],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($event->getRequest()->getPathInfo() === '/sample') {
            return;
        }

        $this->requestId = Uuid::uuid7()->toString();

        $this->logger->info('Start request', [
            'request_id' => $this->requestId,
            'request_uri' => $event->getRequest()->getPathInfo(),
        ]);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($event->getRequest()->getPathInfo() === '/sample') {
            return;
        }

        $this->logger->info('End request', [
            'request_id' => $this->requestId,
            'request_uri' => $event->getRequest()->getPathInfo(),
        ]);

    }
}
