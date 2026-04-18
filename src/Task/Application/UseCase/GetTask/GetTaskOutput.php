<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetTask;

use App\Task\Domain\Model\TaskDetail;

final readonly class GetTaskOutput
{
    public function __construct(
        public TaskDetail $task,
    ) {}
}
