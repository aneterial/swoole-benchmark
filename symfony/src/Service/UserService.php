<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Metrics;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private Connection $connection,
        private Metrics $metrics
    ) {
    }

    public function getUsers(string $name): array
    {
        $qb = $this->connection->createQueryBuilder();

        $data = $qb->select('*')
            ->from('users')
            ->where('name LIKE :name')
            ->setParameter('name', "%$name%")
            ->setMaxResults(100)
            ->fetchAllAssociative();

        $total = $qb->select('COUNT(*)')
            ->from('users')
            ->where('name LIKE :name')
            ->setParameter('name', "%$name%")
            ->fetchOne();

        $this->metrics->save(Metrics::MEMORY_PROCESS, memory_get_usage(true));

        return [
            'data' => $data,
            'total' => $total,
        ];
    }

    public function getUsersV2(string $name): array
    {
        $qb = $this->connection->createQueryBuilder();

        $data = $qb->select('*')
            ->from('users')
            ->where('name LIKE :name')
            ->setParameter('name', "%$name%")
            ->setMaxResults(100)
            ->fetchAllAssociative();

        $total = $qb->select('COUNT(*)')
            ->from('users')
            ->where('name LIKE :name')
            ->setParameter('name', "%$name%")
            ->fetchOne();

        $uuids = array_map(
            static fn (): string => Uuid::uuid7()->toString(),
            range(0, 1000)
        );

        $this->metrics->save(Metrics::MEMORY_PROCESS, memory_get_usage(true));

        return [
            'data' => array_combine(
                array_slice($uuids, 0, count($data)),
                $data
            ),
            'total' => $total,
        ];
    }
}
