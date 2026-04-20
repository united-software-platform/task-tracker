<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetTask;

use App\Task\Application\Repository\TaskReadRepositoryInterface;

final readonly class GetTaskUseCase implements GetTaskUseCaseInterface
{
    public function __construct(
        private TaskReadRepositoryInterface $tasks,
    ) {}

    public function execute(GetTaskInput $input): GetTaskOutput
    {
        return new GetTaskOutput($this->tasks->findById($input->taskId));
    }
}
