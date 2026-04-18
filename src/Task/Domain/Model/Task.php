<?php

declare(strict_types=1);

namespace App\Task\Domain\Model;

final readonly class Task
{
    public function __construct(
        public int $id,
        public int $storyId,
        public string $title,
        public ?string $description,
        public int $status,
        public int $readiness,
    ) {}
}
