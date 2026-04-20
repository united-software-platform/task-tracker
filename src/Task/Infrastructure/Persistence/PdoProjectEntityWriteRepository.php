<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Domain\Model\ProjectEntity;
use App\Task\Domain\Repository\ProjectEntityWriteRepositoryInterface;
use PDO;

final readonly class PdoProjectEntityWriteRepository implements ProjectEntityWriteRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function link(ProjectEntity $entity): void
    {
        $this->pdo->prepare(
            'INSERT INTO core.project_entities (project_id, entity_type_id, entity_id)
             SELECT :project_id, et.id, :entity_id FROM core.entity_types et WHERE et.type = :entity_type',
        )->execute([
            'project_id' => $entity->projectId,
            'entity_id' => $entity->entityId,
            'entity_type' => $entity->entityType->value,
        ]);
    }
}
