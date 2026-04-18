<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetStoryTasks;

final readonly class GetStoryTasksInput
{
    public function __construct(
        public int $storyId,
    ) {}
}
