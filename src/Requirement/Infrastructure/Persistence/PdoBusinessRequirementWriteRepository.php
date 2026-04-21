<?php

declare(strict_types=1);

namespace App\Requirement\Infrastructure\Persistence;

use App\Requirement\Domain\Model\BusinessRequirement;
use App\Requirement\Domain\Repository\BusinessRequirementWriteRepositoryInterface;
use PDO;

final readonly class PdoBusinessRequirementWriteRepository implements BusinessRequirementWriteRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function create(int $projectId, string $code, string $description): BusinessRequirement
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core.business_requirements (code, description) VALUES (:code, :description)
             RETURNING id, code, description',
        );
        $stmt->execute(['code' => $code, 'description' => $description]);

        /** @var array{id: int, code: string, description: string} $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->pdo->prepare(
            'INSERT INTO core.project_entities (project_id, entity_type_id, entity_id)
             SELECT :project_id, et.id, :entity_id FROM core.entity_types et WHERE et.type = \'bt\'',
        )->execute(['project_id' => $projectId, 'entity_id' => $row['id']]);

        return new BusinessRequirement((int) $row['id'], $row['code'], $row['description']);
    }

    public function update(int $id, string $description): void
    {
        $this->pdo->prepare(
            'UPDATE core.business_requirements SET description = :description, updated_at = now() WHERE id = :id',
        )->execute(['description' => $description, 'id' => $id]);
    }
}
