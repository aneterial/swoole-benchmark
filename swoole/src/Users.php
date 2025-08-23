<?php

declare(strict_types=1);

namespace App;

use Swoole\Coroutine;
use Swoole\Database\PDOPool;
use PDO;
use Ramsey\Uuid\Uuid;

final readonly class Users
{
    public function __construct(private PDOPool $db)
    {
    }

    public function getUsers(string $name): array
    {
        $results = [];

        Coroutine::join([
            go(function() use ($name, &$results): void {
                $pdo = $this->db->get();
                try {
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE name LIKE :name LIMIT 100');
                    $stmt->bindValue('name', "%$name%", PDO::PARAM_STR);
                    $stmt->execute();
                    $results['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    unset($stmt);
                } finally {
                    $this->db->put($pdo);
                }
            }),
            go(function() use ($name, &$results): void {
                $pdo = $this->db->get();
                try {
                    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM users WHERE name LIKE :name');
                    $stmt->bindValue('name', "%$name%", PDO::PARAM_STR);
                    $stmt->execute();
                    $count = $stmt->fetch(PDO::FETCH_ASSOC);
                    $results['total'] = $count['total'];
                    unset($stmt);
                } finally {
                    $this->db->put($pdo);
                }
            }),
        ]);

        return $results;
    }

    public function getUsersV2(string $name): array
    {
        $results = [];
        $uuids = [];

        Coroutine::join([
            go(function() use ($name, &$results): void {
                $pdo = $this->db->get();
                try {
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE name LIKE :name LIMIT 100');
                    $stmt->bindValue('name', "%$name%", PDO::PARAM_STR);
                    $stmt->execute();
                    $results['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    unset($stmt);
                } finally {
                    $this->db->put($pdo);
                }
            }),
            go(function() use ($name, &$results): void {
                $pdo = $this->db->get();
                try {
                    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM users WHERE name LIKE :name');
                    $stmt->bindValue('name', "%$name%", PDO::PARAM_STR);
                    $stmt->execute();
                    $count = $stmt->fetch(PDO::FETCH_ASSOC);
                    $results['total'] = $count['total'];
                    unset($stmt);
                } finally {
                    $this->db->put($pdo);
                }
            }),
            go(static function() use (&$uuids): void {
                for ($i = 0; $i < 1000; $i++) {
                    $uuid = Uuid::uuid7()->toString();
                    $uuids[] = $uuid;
                }
            }),
        ]);

        $results['data'] = array_combine(
            array_slice($uuids, 0, count($results['data'])),
            $results['data']
        );

        return $results;
    }
}
