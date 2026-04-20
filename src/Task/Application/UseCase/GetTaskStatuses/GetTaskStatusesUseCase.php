<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetTaskStatuses;

use App\Task\Application\Repository\StatusReadRepositoryInterface;

final readonly class GetTaskStatusesUseCase implements GetTaskStatusesUseCaseInterface
{
    public function __construct(
        private StatusReadRepositoryInterface $statuses,
    ) {}

    public function execute(): GetTaskStatusesOutput
    {
        return new GetTaskStatusesOutput($this->statuses->listAll());
    }
}
