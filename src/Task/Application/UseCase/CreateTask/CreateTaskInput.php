<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateTask;

final readonly class CreateTaskInput
{
    public function __construct(
        public int $storyId,
        public string $title,
        public ?string $description,
    ) {}
}
