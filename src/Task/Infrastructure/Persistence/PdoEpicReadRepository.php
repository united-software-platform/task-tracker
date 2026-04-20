<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Application\Dto\EpicSummary;
use App\Task\Application\Repository\EpicReadRepositoryInterface;
use PDO;

final readonly class PdoEpicReadRepository implements EpicReadRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function listAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT e.id, e.title, COUNT(s.id) AS story_count
             FROM core.epics e
             LEFT JOIN core.stories s ON s.epic_id = e.id
             GROUP BY e.id, e.title
             ORDER BY e.id',
        );

        /** @var list<array{id: int, title: string, story_count: int}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $row) => new EpicSummary((int) $row['id'], $row['title'], (int) $row['story_count']),
            $rows,
        );
    }
}
