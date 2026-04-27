<?php

declare(strict_types=1);

namespace App\Task\Domain\Model;

use App\Shared\Domain\ValueObject\EntityType;

final readonly class ProjectEntity
{
    public function __construct(
        public int $projectId,
        public int $entityId,
        public EntityType $entityType,
    ) {}
}
