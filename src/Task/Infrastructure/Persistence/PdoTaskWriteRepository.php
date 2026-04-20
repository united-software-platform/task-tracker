<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Domain\Model\Task;
use App\Task\Domain\Repository\TaskWriteRepositoryInterface;
use PDO;

final readonly class PdoTaskWriteRepository implements TaskWriteRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function create(Task $task): Task
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core.tasks (code, story_id, title, description, status, readiness, model)
             VALUES (:code, :story_id, :title, :description, :status, :readiness, :model)
             RETURNING id',
        );
        $stmt->execute([
            'code' => $task->code,
            'story_id' => $task->storyId,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'readiness' => $task->readiness,
            'model' => $task->model,
        ]);

        /** @var array{id: int} $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = (int) $row['id'];

        return new Task(
            id: $id,
            code: $task->code,
            projectId: $task->projectId,
            storyId: $task->storyId,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            readiness: $task->readiness,
            model: $task->model,
        );
    }

    public function update(Task $task): void
    {
        $this->pdo->prepare(
            'UPDATE core.tasks
             SET title = :title, description = :description, status = :status,
                 readiness = :readiness, model = :model, updated_at = now()
             WHERE id = :id',
        )->execute([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'readiness' => $task->readiness,
            'model' => $task->model,
        ]);
    }
}
