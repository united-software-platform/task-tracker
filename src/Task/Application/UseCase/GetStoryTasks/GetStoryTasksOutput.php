<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetStoryTasks;

use App\Task\Application\Dto\TaskSummary;

final readonly class GetStoryTasksOutput
{
    public function __construct(
        /** @var list<TaskSummary> */
        public array $tasks,
    ) {}
}
