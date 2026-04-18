<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Domain\Model\Story;
use App\Task\Domain\Model\StorySummary;
use App\Task\Domain\Repository\StoryRepositoryInterface;
use PDO;

final readonly class PdoStoryRepository implements StoryRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function create(int $epicId, string $title, ?string $description): Story
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core.stories (epic_id, title, description) VALUES (:epic_id, :title, :description) RETURNING id, epic_id, title, description',
        );
        $stmt->execute(['epic_id' => $epicId, 'title' => $title, 'description' => $description]);

        /** @var array{id: int, epic_id: int, title: string, description: null|string} $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Story((int) $row['id'], (int) $row['epic_id'], $row['title'], $row['description']);
    }

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
