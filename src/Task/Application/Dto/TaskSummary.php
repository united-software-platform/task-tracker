<?php

declare(strict_types=1);

namespace App\Task\Application\Dto;

final readonly class TaskSummary
{
    public function __construct(
        public int $id,
        public string $title,
        public int $status,
        public int $readiness,
    ) {}
}
