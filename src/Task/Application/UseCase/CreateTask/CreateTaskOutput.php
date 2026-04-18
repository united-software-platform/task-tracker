<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateTask;

final readonly class CreateTaskOutput
{
    public function __construct(
        public int $id,
        public string $title,
        public int $status,
    ) {}
}
