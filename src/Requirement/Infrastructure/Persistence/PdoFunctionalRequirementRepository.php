<?php

declare(strict_types=1);

namespace App\Requirement\Infrastructure\Persistence;

use App\Requirement\Domain\Model\FunctionalRequirement;
use App\Requirement\Domain\Model\FunctionalRequirementDetail;
use App\Requirement\Domain\Repository\FunctionalRequirementRepositoryInterface;
use PDO;
use RuntimeException;

final readonly class PdoFunctionalRequirementRepository implements FunctionalRequirementRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function listByProjectId(int $projectId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT fr.id, fr.code, fr.description
             FROM core.functional_requirements fr
             INNER JOIN core.project_entities pe ON pe.entity_id = fr.id
             INNER JOIN core.entity_types et ON et.id = pe.entity_type_id AND et.type = \'ft\'
             WHERE pe.project_id = :project_id
             ORDER BY fr.id',
        );
        $stmt->execute(['project_id' => $projectId]);

        /** @var list<array{id: int, code: string, description: string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $row) => new FunctionalRequirement((int) $row['id'], $row['code'], $row['description']),
            $rows,
        );
    }

    public function findById(int $id): FunctionalRequirementDetail
    {
        $stmt = $this->pdo->prepare(
            'SELECT fr.id, fr.code, fr.description, pe.project_id,
                    to_char(fr.created_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS created_at,
                    to_char(fr.updated_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS updated_at
             FROM core.functional_requirements fr
             INNER JOIN core.project_entities pe ON pe.entity_id = fr.id
             INNER JOIN core.entity_types et ON et.id = pe.entity_type_id AND et.type = \'ft\'
             WHERE fr.id = :id',
        );
        $stmt->execute(['id' => $id]);

        /** @var array{id: int, code: string, description: string, project_id: int, created_at: string, updated_at: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $row) {
            throw new RuntimeException(sprintf('FunctionalRequirement #%d not found', $id));
        }

        return new FunctionalRequirementDetail(
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
        $stmt = $this->pdo->query("SELECT nextval('core.functional_requirements_id_seq')");

        return (int) $stmt->fetchColumn();
    }

    public function create(FunctionalRequirement $requirement, int $projectId): void
    {
        $this->pdo->prepare(
            'INSERT INTO core.functional_requirements (id, code, description) VALUES (:id, :code, :description)',
        )->execute(['id' => $requirement->id, 'code' => $requirement->code, 'description' => $requirement->description]);

        $this->pdo->prepare(
            'INSERT INTO core.project_entities (project_id, entity_type_id, entity_id)
             VALUES (:project_id, (SELECT id FROM core.entity_types WHERE type = \'ft\'), :entity_id)',
        )->execute(['project_id' => $projectId, 'entity_id' => $requirement->id]);
    }

    public function update(int $id, string $description): void
    {
        $this->pdo->prepare(
            'UPDATE core.functional_requirements SET description = :description, updated_at = now() WHERE id = :id',
        )->execute(['description' => $description, 'id' => $id]);
    }
}
