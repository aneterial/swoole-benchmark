#!/usr/bin/env php
<?php

!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require_once BASE_PATH . '/vendor/autoload.php';

!defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', Hyperf\Engine\DefaultOption::hookFlags());

(static function (): void {
    Hyperf\Di\ClassLoader::init();
    /** @var \Hyperf\Di\Container $container */
    $container = require BASE_PATH . '/config/container.php';

    $application = $container->get(Hyperf\Contract\ApplicationInterface::class);
    $application->run();
})();
