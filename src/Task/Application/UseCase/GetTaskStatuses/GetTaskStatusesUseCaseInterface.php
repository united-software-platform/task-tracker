<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetTaskStatuses;

interface GetTaskStatusesUseCaseInterface
{
    public function execute(): GetTaskStatusesOutput;
}
