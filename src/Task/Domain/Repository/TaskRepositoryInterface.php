<?php

declare(strict_types=1);

namespace App\Task\Domain\Repository;

use App\Task\Domain\Model\Task;
use App\Task\Domain\Model\TaskDetail;
use App\Task\Domain\Model\TaskSummary;

interface TaskRepositoryInterface
{
    public function create(int $storyId, string $title, ?string $description): Task;

    public function findById(int $id): TaskDetail;

    public function update(int $id, ?string $title, ?string $description, ?int $readiness, ?int $status): void;

    /** @return list<TaskSummary> */
    public function listByStoryId(int $storyId): array;

    /** @return list<TaskSummary> */
    public function listByProjectId(int $projectId): array;
}
