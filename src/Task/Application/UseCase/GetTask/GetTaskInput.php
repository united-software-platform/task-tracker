<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetTask;

final readonly class GetTaskInput
{
    public function __construct(
        public int $taskId,
    ) {}
}
