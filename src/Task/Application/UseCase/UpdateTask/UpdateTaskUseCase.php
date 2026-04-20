<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\UpdateTask;

use App\Task\Application\Repository\TaskReadRepositoryInterface;
use App\Task\Domain\Model\Task;
use App\Task\Domain\Repository\TaskWriteRepositoryInterface;

final readonly class UpdateTaskUseCase implements UpdateTaskUseCaseInterface
{
    public function __construct(
        private TaskWriteRepositoryInterface $tasks,
        private TaskReadRepositoryInterface $taskReader,
    ) {}

    public function execute(UpdateTaskInput $input): void
    {
        $current = $this->taskReader->findById($input->taskId);

        $this->tasks->update(new Task(
            id: $current->id,
            code: $current->code,
            projectId: $current->projectId,
            storyId: $current->storyId,
            title: $input->title ?? $current->title,
            description: $input->description ?? $current->description,
            status: $input->status ?? $current->status,
            readiness: $input->readiness ?? $current->readiness,
            model: $input->model ?? $current->model,
        ));
    }
}
