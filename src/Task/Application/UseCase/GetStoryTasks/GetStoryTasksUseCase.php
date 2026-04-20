<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetStoryTasks;

use App\Task\Application\Repository\TaskReadRepositoryInterface;

final readonly class GetStoryTasksUseCase implements GetStoryTasksUseCaseInterface
{
    public function __construct(
        private TaskReadRepositoryInterface $tasks,
    ) {}

    public function execute(GetStoryTasksInput $input): GetStoryTasksOutput
    {
        return new GetStoryTasksOutput($this->tasks->listByStoryId($input->storyId));
    }
}
