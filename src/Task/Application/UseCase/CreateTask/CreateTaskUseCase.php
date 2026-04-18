<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateTask;

use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class CreateTaskUseCase implements CreateTaskUseCaseInterface
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {}

    public function execute(CreateTaskInput $input): CreateTaskOutput
    {
        $task = $this->tasks->create($input->storyId, $input->title, $input->description);

        return new CreateTaskOutput($task->id, $task->title, $task->status);
    }
}
