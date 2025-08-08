<?php

declare(strict_types=1);

namespace App;

use Swoole\Coroutine;
use Swoole\Database\PDOPool;
use PDO;

final readonly class Users
{
    public function __construct(private PDOPool $pool)
    {
    }

    public function getUsers(string $name): array
    {
        $results = [];

        Coroutine::join([
            go(function() use ($name, &$results): void {
                $pdo = $this->pool->get();
                try {
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE name LIKE :name LIMIT 100');
                    $stmt->bindValue('name', "%$name%", PDO::PARAM_STR);
                    $stmt->execute();
                    $results['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    unset($stmt);
                } finally {
                    $this->pool->put($pdo);
                }
            }),
            go(function() use ($name, &$results): void {
                $pdo = $this->pool->get();
                try {
                    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM users WHERE name LIKE :name');
                    $stmt->bindValue('name', "%$name%", PDO::PARAM_STR);
                    $stmt->execute();
                    $count = $stmt->fetch(PDO::FETCH_ASSOC);
                    $results['total'] = $count['total'];
                    unset($stmt);
                } finally {
                    $this->pool->put($pdo);
                }
            })
        ]);

        return $results;
    }
}
