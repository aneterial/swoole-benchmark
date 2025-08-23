<?php

declare(strict_types=1);

use App\Http\Controllers\SampleController;
use App\Http\Middlewares\RequestLogMiddleware;
use App\Http\Controllers\UsersController;
use App\Http\Middlewares\MetricsMiddleware;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::get(uri: '/sample', action: [SampleController::class, 'index']);

Route::get(uri: '/users/{name}', action: [UsersController::class, 'index'])
    ->where('name', '[a-zA-Z0-9]+')
    ->middleware([RequestLogMiddleware::class, MetricsMiddleware::class]);
Route::get(uri: '/v2/users/{name}', action: [UsersController::class, 'indexV2'])
    ->where('name', '[a-zA-Z0-9]+')
    ->middleware([RequestLogMiddleware::class, MetricsMiddleware::class]);

Route::get(uri: '/metrics/{name}', action: [MetricsController::class, 'index'])
    ->where('key', '[a-zA-Z0-9]+');
