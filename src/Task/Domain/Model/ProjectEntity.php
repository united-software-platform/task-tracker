<?php

declare(strict_types=1);

namespace App\Task\Domain\Model;

final readonly class ProjectEntity
{
    public function __construct(
        public int $projectId,
        public int $entityId,
        public EntityType $entityType,
    ) {}
}
