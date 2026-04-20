<?php

declare(strict_types=1);

namespace App\Requirement\Infrastructure\Persistence;

use App\Requirement\Domain\Model\BusinessRequirement;
use App\Requirement\Domain\Model\BusinessRequirementDetail;
use App\Requirement\Domain\Repository\BusinessRequirementRepositoryInterface;
use PDO;
use RuntimeException;

final readonly class PdoBusinessRequirementRepository implements BusinessRequirementRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function listByProjectId(int $projectId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT br.id, br.code, br.description
             FROM core.business_requirements br
             INNER JOIN core.project_entities pe ON pe.entity_id = br.id
             INNER JOIN core.entity_types et ON et.id = pe.entity_type_id AND et.type = \'bt\'
             WHERE pe.project_id = :project_id
             ORDER BY br.id',
        );
        $stmt->execute(['project_id' => $projectId]);

        /** @var list<array{id: int, code: string, description: string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $row) => new BusinessRequirement((int) $row['id'], $row['code'], $row['description']),
            $rows,
        );
    }

    public function findById(int $id): BusinessRequirementDetail
    {
        $stmt = $this->pdo->prepare(
            'SELECT br.id, br.code, br.description, pe.project_id,
                    to_char(br.created_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS created_at,
                    to_char(br.updated_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS updated_at
             FROM core.business_requirements br
             INNER JOIN core.project_entities pe ON pe.entity_id = br.id
             INNER JOIN core.entity_types et ON et.id = pe.entity_type_id AND et.type = \'bt\'
             WHERE br.id = :id',
        );
        $stmt->execute(['id' => $id]);

        /** @var array{id: int, code: string, description: string, project_id: int, created_at: string, updated_at: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $row) {
            throw new RuntimeException(sprintf('BusinessRequirement #%d not found', $id));
        }

        return new BusinessRequirementDetail(
            (int) $row['id'],
            $row['code'],
            $row['description'],
            (int) $row['project_id'],
            $row['created_at'],
            $row['updated_at'],
        );
    }

    public function nextId(): int
    {
        $stmt = $this->pdo->query("SELECT nextval('core.business_requirements_id_seq')");

        return (int) $stmt->fetchColumn();
    }

    public function create(BusinessRequirement $requirement, int $projectId): void
    {
        $this->pdo->prepare(
            'INSERT INTO core.business_requirements (id, code, description) VALUES (:id, :code, :description)',
        )->execute(['id' => $requirement->id, 'code' => $requirement->code, 'description' => $requirement->description]);

        $this->pdo->prepare(
            'INSERT INTO core.project_entities (project_id, entity_type_id, entity_id)
             VALUES (:project_id, (SELECT id FROM core.entity_types WHERE type = \'bt\'), :entity_id)',
        )->execute(['project_id' => $projectId, 'entity_id' => $requirement->id]);
    }

    public function update(int $id, string $description): void
    {
        $this->pdo->prepare(
            'UPDATE core.business_requirements SET description = :description, updated_at = now() WHERE id = :id',
        )->execute(['description' => $description, 'id' => $id]);
    }
}
