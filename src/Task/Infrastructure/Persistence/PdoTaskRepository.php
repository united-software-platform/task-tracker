<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Domain\Model\Task;
use App\Task\Domain\Model\TaskDetail;
use App\Task\Domain\Model\TaskSummary;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use PDO;
use RuntimeException;

final readonly class PdoTaskRepository implements TaskRepositoryInterface
{
    private const STATUS_NEW = 1;

    public function __construct(
        private PDO $pdo,
    ) {}

    public function create(int $storyId, string $title, ?string $description): Task
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core.tasks (story_id, title, description, status, readiness) VALUES (:story_id, :title, :description, :status, 0) RETURNING id, story_id, title, description, status, readiness',
        );
        $stmt->execute([
            'story_id' => $storyId,
            'title' => $title,
            'description' => $description,
            'status' => self::STATUS_NEW,
        ]);

        /** @var array{id: int, story_id: int, title: string, description: null|string, status: int, readiness: int} $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Task(
            (int) $row['id'],
            (int) $row['story_id'],
            $row['title'],
            $row['description'],
            (int) $row['status'],
            (int) $row['readiness'],
        );
    }

    public function findById(int $id): TaskDetail
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, story_id, title, description, status, readiness,
                    to_char(created_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS created_at,
                    to_char(updated_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS updated_at
             FROM core.tasks
             WHERE id = :id',
        );
        $stmt->execute(['id' => $id]);

        /** @var array{id: int, story_id: int, title: string, description: null|string, status: int, readiness: int, created_at: string, updated_at: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $row) {
            throw new RuntimeException(sprintf('Task #%d not found', $id));
        }

        return new TaskDetail(
            (int) $row['id'],
            (int) $row['story_id'],
            $row['title'],
            $row['description'],
            (int) $row['status'],
            (int) $row['readiness'],
            $row['created_at'],
            $row['updated_at'],
        );
    }

    public function update(int $id, ?string $title, ?string $description, ?int $readiness, ?int $status): void
    {
        $sets = ['updated_at = now()'];
        $params = ['id' => $id];

        if (null !== $title) {
            $sets[] = 'title = :title';
            $params['title'] = $title;
        }
        if (null !== $description) {
            $sets[] = 'description = :description';
            $params['description'] = $description;
        }
        if (null !== $readiness) {
            $sets[] = 'readiness = :readiness';
            $params['readiness'] = $readiness;
        }
        if (null !== $status) {
            $sets[] = 'status = :status';
            $params['status'] = $status;
        }

        $this->pdo->prepare(
            'UPDATE core.tasks SET ' . implode(', ', $sets) . ' WHERE id = :id',
        )->execute($params);
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
