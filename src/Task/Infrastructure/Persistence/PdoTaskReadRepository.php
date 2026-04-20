<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Application\Dto\TaskDetail;
use App\Task\Application\Dto\TaskSummary;
use App\Task\Application\Repository\TaskReadRepositoryInterface;
use PDO;
use RuntimeException;

final readonly class PdoTaskReadRepository implements TaskReadRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function findById(int $id): TaskDetail
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.id, t.code, t.story_id, t.title, t.description, t.status, t.readiness, t.model,
                    pe.project_id,
                    to_char(t.created_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS created_at,
                    to_char(t.updated_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS updated_at
             FROM core.tasks t
             JOIN core.project_entities pe ON pe.entity_id = t.id
             JOIN core.entity_types et ON et.id = pe.entity_type_id AND et.type = \'task\'
             WHERE t.id = :id',
        );
        $stmt->execute(['id' => $id]);

        /** @var array{id: int, code: string, project_id: int, story_id: int, title: string, description: null|string, status: int, readiness: int, model: null|string, created_at: string, updated_at: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $row) {
            throw new RuntimeException(sprintf('Task #%d not found', $id));
        }

        return new TaskDetail(
            id: (int) $row['id'],
            code: $row['code'],
            projectId: (int) $row['project_id'],
            storyId: (int) $row['story_id'],
            title: $row['title'],
            description: $row['description'],
            status: (int) $row['status'],
            readiness: (int) $row['readiness'],
            model: $row['model'] ?? null,
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    public function listByStoryId(int $storyId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, title, status, readiness
             FROM core.tasks
             WHERE story_id = :story_id
             ORDER BY id',
        );
        $stmt->execute(['story_id' => $storyId]);

        /** @var list<array{id: int, title: string, status: int, readiness: int}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $row) => new TaskSummary((int) $row['id'], $row['title'], (int) $row['status'], (int) $row['readiness']),
            $rows,
        );
    }
}
