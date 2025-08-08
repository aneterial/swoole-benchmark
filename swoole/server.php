<?php

declare(strict_types=1);

require_once "./vendor/autoload.php";

use App\AsyncLogs;
use App\Handler;
use App\Metrics;
use App\Users;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Ramsey\Uuid\Uuid;
use Swoole\Coroutine;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

use function FastRoute\simpleDispatcher;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$pool = new PDOPool(
    config: new PDOConfig()
        ->withDriver('pgsql')
        ->withHost('db')
        ->withPort(5432)
        ->withDbName('test')
        ->withCharset('utf8')
        ->withUsername('test')
        ->withPassword('test'),
    size: 100
);
$metrics = new Metrics();
$handler = new Handler($metrics, new Users($pool));

$server = new Server(port: 8080, mode: SWOOLE_PROCESS);
$server->set([
    'enable_coroutine' => true,
    'worker_num' => 4,
]);

$dispatcher = simpleDispatcher(static function (RouteCollector $r) use ($handler): void {
    $r->addRoute('GET', '/metrics/{name:[a-zA-Z0-9]+}', [$handler, 'metrics']);
    $r->addRoute('GET', '/users/{name:[a-zA-Z0-9]+}', [$handler, 'users']);
    $r->addRoute('GET', '/sample', [$handler, 'sample']);
});

$server->on('request', static function (Request $request, Response $response) use ($dispatcher, $metrics): void {
    try {
        $uri = $request->server['request_uri'];
        $routeInfo = $dispatcher->dispatch(httpMethod: $request->server['request_method'], uri: $uri);

        if (($routeInfo[0] ?? 0) !== Dispatcher::FOUND) {
            $response->status(404);
            $response->end(json_encode(['error' => 'Not found'], JSON_UNESCAPED_UNICODE));
            return;
        }

        [, $handler, $vars] = $routeInfo;

        $loadTest = str_contains($uri, '/users/');
        if ($loadTest) {
            $requestId = Uuid::uuid7()->toString();
            $metrics->save(Metrics::MEMORY_START, memory_get_usage());
            printf("Start request [%s]: %s\n", $requestId, $uri);
        }

        $result = $handler($vars, $request);

        $response->header('Content-Type', 'application/json; charset=utf-8');
        $response->end($result);

        if ($loadTest) {
            printf("End request [%s]: %s\n", $requestId, $uri);
            $metrics->save(Metrics::MEMORY_END, memory_get_usage());
        }

    } catch (Throwable $e) {
        var_dump($e->getMessage());
        $response->status(500);
        $response->end(json_encode(['error' => 'Internal server error']));
    }
});

$server->start();
