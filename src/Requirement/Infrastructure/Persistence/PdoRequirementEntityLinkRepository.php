<?php

declare(strict_types=1);

namespace App\Requirement\Infrastructure\Persistence;

use App\Requirement\Application\Repository\RequirementEntityLinkRepositoryInterface;
use App\Shared\Domain\ValueObject\EntityType;
use PDO;

final readonly class PdoRequirementEntityLinkRepository implements RequirementEntityLinkRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function link(int $entityId, EntityType $entityType): void
    {
        $this->pdo->prepare(
            'INSERT INTO core.project_entities (entity_type_id, entity_id)
             SELECT et.id, :entity_id FROM core.entity_types et WHERE et.type = :entity_type',
        )->execute([
            'entity_id' => $entityId,
            'entity_type' => $entityType->value,
        ]);
    }
}
