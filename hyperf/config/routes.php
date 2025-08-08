<?php

declare(strict_types=1);

use App\Http\Controller\MetricsController;
use App\Http\Controller\SampleController;
use App\Http\Controller\UsersController;
use App\Http\Middleware\MetricsMiddleware;
use App\Http\Middleware\RequestLogMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addServer('http', static function (): void {
    Router::get(route: '/favicon.ico', handler: static fn (): string => '');

    Router::get(route: '/sample', handler: [SampleController::class, 'index']);

    Router::addGroup(prefix: '/users', options: ['middleware' => [MetricsMiddleware::class, RequestLogMiddleware::class]], callback: static function (): void {
        Router::get(route: '/{name:[a-zA-Z0-9]+}', handler: [UsersController::class, 'index']);
    });

    Router::addGroup(prefix: '/metrics', callback: static function (): void {
        Router::get(route: '/{name:[a-zA-Z0-9]+}', handler: [MetricsController::class, 'index']);
    });
});
