<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\Metrics;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class MetricsMiddleware implements EventSubscriberInterface
{
    public function __construct(private Metrics $metrics)
    {
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

        $this->metrics->save(Metrics::MEMORY_START, memory_get_usage(true));
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($event->getRequest()->getPathInfo() === '/sample') {
            return;
        }

        $this->metrics->save(Metrics::MEMORY_END, memory_get_usage(true));
    }
}
