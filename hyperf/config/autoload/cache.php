<?php

declare(strict_types=1);

return [
    'default' => [
        'driver' => Hyperf\Cache\Driver\MemoryDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'skip_cache_results' => [],
    ],

    'context' => [
        'driver' => Hyperf\Cache\Driver\CoroutineMemoryDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
    ],

    'redis' => [
        'driver' => Hyperf\Cache\Driver\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'c:',
        'skip_cache_results' => [],
    ],
];
