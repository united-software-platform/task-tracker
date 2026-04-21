<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Application\Dto\StatusView;
use App\Task\Application\Repository\StatusReadRepositoryInterface;
use PDO;

final readonly class PdoStatusReadRepository implements StatusReadRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, name FROM core.statuses ORDER BY id');

        /** @var list<array{id: int, name: string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $row) => new StatusView($row['id'], $row['name']),
            $rows,
        );
    }
}
