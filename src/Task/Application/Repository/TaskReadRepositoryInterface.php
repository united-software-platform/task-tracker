<?php

declare(strict_types=1);

namespace App\Task\Application\Repository;

use App\Task\Application\Dto\TaskDetail;
use App\Task\Application\Dto\TaskSummary;

interface TaskReadRepositoryInterface
{
    public function findById(int $id): TaskDetail;

    /** @return list<TaskSummary> */
    public function listByStoryId(int $storyId): array;
}
