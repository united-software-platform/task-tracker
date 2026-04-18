<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\UpdateTask;

final readonly class UpdateTaskInput
{
    public function __construct(
        public int $taskId,
        public ?string $title = null,
        public ?string $description = null,
        public ?int $readiness = null,
        public ?int $status = null,
    ) {}
}
