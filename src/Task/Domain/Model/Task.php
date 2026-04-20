<?php

declare(strict_types=1);

namespace App\Task\Domain\Model;

final readonly class Task
{
    public const int STATUS_NEW = 1;

    public function __construct(
        public int $id,
        public string $code,
        public int $projectId,
        public int $storyId,
        public string $title,
        public ?string $description,
        public int $status,
        public int $readiness,
        public ?string $model,
    ) {}
}
