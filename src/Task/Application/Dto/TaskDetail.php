<?php

declare(strict_types=1);

namespace App\Task\Application\Dto;

final readonly class TaskDetail
{
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
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
