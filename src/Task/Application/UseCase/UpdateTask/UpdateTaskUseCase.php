<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\UpdateTask;

use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class UpdateTaskUseCase implements UpdateTaskUseCaseInterface
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {}

    public function execute(UpdateTaskInput $input): void
    {
        $this->tasks->update(
            $input->taskId,
            $input->title,
            $input->description,
            $input->readiness,
            $input->status,
        );
    }
}
