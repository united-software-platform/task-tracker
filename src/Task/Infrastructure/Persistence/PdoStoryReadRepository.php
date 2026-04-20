<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Application\Dto\StorySummary;
use App\Task\Application\Repository\StoryReadRepositoryInterface;
use PDO;

final readonly class PdoStoryReadRepository implements StoryReadRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function listByEpicId(int $epicId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.id, s.title, COALESCE(ROUND(AVG(t.readiness))::int, 0) AS avg_readiness
             FROM core.stories s
             LEFT JOIN core.tasks t ON t.story_id = s.id
             WHERE s.epic_id = :epic_id
             GROUP BY s.id, s.title
             ORDER BY s.id',
        );
        $stmt->execute(['epic_id' => $epicId]);

        /** @var list<array{id: int, title: string, avg_readiness: int}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $row) => new StorySummary((int) $row['id'], $row['title'], (int) $row['avg_readiness']),
            $rows,
        );
    }
}
