<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\UserService;
use App\Services\UserServiceInterface;
use App\Services\MetricsService;
use App\Services\MetricsServiceInterface;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserServiceInterface::class => UserService::class,
        MetricsServiceInterface::class => MetricsService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
