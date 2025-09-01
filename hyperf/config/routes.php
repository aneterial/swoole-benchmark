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
    Router::addGroup(prefix: '/v2', options: ['middleware' => [MetricsMiddleware::class, RequestLogMiddleware::class]], callback: static function (): void {
        Router::get(route: '/users/{name:[a-zA-Z0-9]+}', handler: [UsersController::class, 'indexV2']);
    });

    Router::addGroup(prefix: '/metrics', callback: static function (): void {
        Router::get(route: '/{name:[a-zA-Z0-9]+}', handler: [MetricsController::class, 'index']);
    });



    // // Выполнятся все корутины перед return,
    // // т.к. в основной корутине есть неблокирующая операция, переключающая контекст выполнения
    // Router::get(route: '/example/1', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {

    //     $i = 0;

    //     go(function () use (&$i, $logger): void {
    //         $logger->info('1');
    //         if ($i === 10) {
    //             $i +=2;
    //         }
    //         $logger->info('4');
    //     });
    //     go(function () use (&$i, $logger): void {
    //         $logger->info('2');
    //         if ($i === 12) {
    //             $i +=3;
    //         }
    //         $logger->info('5');
    //     });
    //     go(function () use (&$i): void {
    //         if ($i === 0) {
    //             $i = 10;
    //         }
    //     });
    //     $logger->info('3');
    //     $logger->info('6');

    //     return $i; // 15
    // });

    // // Перед выходом успеет выполниться только третья корутина,
    // // т.к. в основной корутине нет переключения контекста выполнения
    // Router::get(route: '/example/2', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {
    //     $i = 0;

    //     go(function () use (&$i, $logger): void {
    //         $logger->info('1');
    //         if ($i === 10) {
    //             $i +=2;
    //         }
    //         $logger->info('4');
    //     });
    //     go(function () use (&$i, $logger): void {
    //         $logger->info('2');
    //         if ($i === 12) {
    //             $i +=3;
    //         }
    //         $logger->info('5');
    //     });
    //     go(function () use (&$i): void {
    //         if ($i === 0) {
    //             $i = 10;
    //         }
    //     });

    //     return $i; // 10
    // });

    // // корутины выполняются последовательно не устраивая гонки
    // Router::get(route: '/example/3', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {

    //     $i = 0;

    //     go(function () use (&$i): void {
    //         foreach (range(1, 10000) as $_) {
    //             $i++;
    //         }
    //     });
    //     go(function () use (&$i): void {
    //         foreach (range(1, 10000) as $_) {
    //             $i++;
    //         }
    //     });
    //     go(function () use (&$i): void {
    //         foreach (range(1, 10000) as $_) {
    //             $i++;
    //         }
    //     });

    //     return $i; // 30000
    // });

    // // defer выполняется после выхода из корутины, а не из функции
    // Router::get(route: '/example/4', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {

    //     $callable = function () use ($logger): bool {
    //         defer(function () use ($logger): void {
    //             $logger->info('defer');
    //         });
    //         defer(fn () => $logger->info('defer'));

    //         return true;
    //     };

    //     $callable();

    //     go($callable);


    //     return 0;
    // });


    // // Запись в свободный канал не блокирует текущую корутину
    // Router::get(route: '/example/5', handler: static function (): mixed {
    //     $chan = new \Hyperf\Engine\Channel();

    //     $chan->push(10);

    //     return $chan->pop(); // 10
    // });
    // Router::get(route: '/example/5.1', handler: static function (): mixed {
    //     $chan = new \Hyperf\Engine\Channel();

    //     $chan->push(10);

    //     return 0; // Так тоже можно, но не нужно, объект $chan уничтожается, deadlock не будет
    // });

    // // Запись в заполненный канал блокирует и переключает текущую корутину
    // Router::get(route: '/example/6', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {
    //     $chan = new \Hyperf\Engine\Channel();

    //     $chan->push(10);
    //     $logger->info('1');
    //     $chan->push(10); // [FATAL ERROR]: all coroutines (count: 1) are asleep - deadlock!
    //     $logger->info('2');

    //     return $chan->pop();
    // });

    // // Запись в свободный канал не переключает корутину
    // // Чтение из непустого канала так же не переключает коуртину
    // Router::get(route: '/example/7', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {
    //     $i = 0;
    //     $chan = new \Hyperf\Engine\Channel();

    //     go(function () use (&$i, $chan, $logger): void {
    //         $chan->push(10);
    //         $chan->pop();
    //         $logger->info('1');
    //         while (++$i < 2_000_000_000) {}
    //         $logger->info('3');
    //     });
    //     $logger->info('2');
    //     $logger->info('4');

    //     return $i; // 2000000000
    // });

    // // Чтение из пустого канала блокирует и переключает корутину
    // // Запись в ожидающий чтения канал переключает корутину
    // Router::get(route: '/example/8', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {
    //     $i = 0;
    //     $chan = new \Hyperf\Engine\Channel();

    //     go(function () use (&$i, $chan, $logger): void {
    //         $chan->pop(); // блокируется до записи и переключает корутину
    //         $logger->info('2');
    //         while (++$i < 2_000_000_000) {}
    //         $logger->info('4');
    //     });
    //     $logger->info('1');
    //     $chan->push(10); // запись с переключением корутины
    //     $chan->push(10); // запись без переключения корутины
    //     $logger->info('3');
    //     $logger->info('5');

    //     return $i; // 2000000000
    // });

    // // Чтение из ожидающего записи канала переключает корутину
    // Router::get(route: '/example/9', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {
    //     $i = 0;
    //     $chan = new \Hyperf\Engine\Channel();

    //     go(function () use (&$i, $chan, $logger): void {
    //         $chan->push(10); // свободная запись без переключения корутины
    //         $chan->push(10); // блокируется до чтения и переключает корутину
    //         while (++$i < 2_000_000_000) {}
    //         $logger->info('3');
    //     });

    //     $logger->info('1');
    //     $logger->info('2');
    //     $chan->pop(); // чтение с переключением корутины
    //     $logger->info('4');

    //     return $i; // 2000000000
    // });

    // // мьютексы реализованы через каналы
    // Router::get(route: '/example/10', handler: static function (\Psr\Log\LoggerInterface $logger): mixed {
    //     $i = 0;

    //     go(function () use (&$i, $logger): void {
    //         \Hyperf\Coroutine\Mutex::lock('key'); // Свободная запись в канал без переключения
    //         $logger->info('1');
    //         $logger->info('3');
    //         \Hyperf\Coroutine\Mutex::unlock('key'); // чтение с переключением корутины, т.к. есть ожидающий записи
    //         $logger->info('5');
    //         while (++$i < 2_000_000_000) {}
    //     });

    //     $logger->info('2');
    //     \Hyperf\Coroutine\Mutex::lock('key'); // блокируется до чтения и переключает корутину
    //     $logger->info('4');
    //     $logger->info('6');

    //     return $i; // 2000000000 - !ВАЖНО! т.к. каналы в мьютексе хранятся в статичном свойстве
    //             // при выходе из функции объект канала не уничтожен, второй запрос на ручку приведет к deadlock!
    // });
});
