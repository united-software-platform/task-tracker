<?php

declare(strict_types=1);

namespace App\Requirement\Infrastructure\Persistence;

use App\Requirement\Domain\Model\NonFunctionalRequirement;
use App\Requirement\Domain\Model\NonFunctionalRequirementDetail;
use App\Requirement\Domain\Model\NonFunctionalRequirementType;
use App\Requirement\Domain\Repository\NonFunctionalRequirementRepositoryInterface;
use PDO;
use RuntimeException;

final readonly class PdoNonFunctionalRequirementRepository implements NonFunctionalRequirementRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function listByProjectId(int $projectId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT nfr.id, nfr.code, nfr.type, nfr.description
             FROM core.non_functional_requirements nfr
             INNER JOIN core.project_entities pe ON pe.entity_id = nfr.id
             INNER JOIN core.entity_types et ON et.id = pe.entity_type_id AND et.type = \'nft\'
             WHERE pe.project_id = :project_id
             ORDER BY nfr.id',
        );
        $stmt->execute(['project_id' => $projectId]);

        /** @var list<array{id: int, code: string, type: string, description: string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $row) => new NonFunctionalRequirement(
                (int) $row['id'],
                $row['code'],
                NonFunctionalRequirementType::from($row['type']),
                $row['description'],
            ),
            $rows,
        );
    }

    public function findById(int $id): NonFunctionalRequirementDetail
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, code, type, description, acceptance_criteria,
                    to_char(created_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS created_at,
                    to_char(updated_at AT TIME ZONE \'UTC\', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS updated_at
             FROM core.non_functional_requirements
             WHERE id = :id',
        );
        $stmt->execute(['id' => $id]);

        /** @var array{id: int, code: string, type: string, description: string, acceptance_criteria: null|string, created_at: string, updated_at: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $row) {
            throw new RuntimeException(sprintf('NonFunctionalRequirement #%d not found', $id));
        }

        return new NonFunctionalRequirementDetail(
            (int) $row['id'],
            $row['code'],
            NonFunctionalRequirementType::from($row['type']),
            $row['description'],
            $row['acceptance_criteria'],
            $row['created_at'],
            $row['updated_at'],
        );
    }

    public function nextId(): int
    {
        $stmt = $this->pdo->query("SELECT nextval('core.non_functional_requirements_id_seq')");

        return (int) $stmt->fetchColumn();
    }

    public function create(NonFunctionalRequirement $requirement, int $projectId, ?string $acceptanceCriteria): void
    {
        $this->pdo->prepare(
            'INSERT INTO core.non_functional_requirements (id, code, type, description, acceptance_criteria)
             VALUES (:id, :code, :type, :description, :acceptance_criteria)',
        )->execute([
            'id' => $requirement->id,
            'code' => $requirement->code,
            'type' => $requirement->type->value,
            'description' => $requirement->description,
            'acceptance_criteria' => $acceptanceCriteria,
        ]);

        $this->pdo->prepare(
            'INSERT INTO core.project_entities (project_id, entity_type_id, entity_id)
             VALUES (:project_id, (SELECT id FROM core.entity_types WHERE type = \'nft\'), :entity_id)',
        )->execute(['project_id' => $projectId, 'entity_id' => $requirement->id]);
    }

    public function update(int $id, ?string $description, ?NonFunctionalRequirementType $type, ?string $acceptanceCriteria): void
    {
        $sets = ['updated_at = now()'];
        $params = ['id' => $id];

        if (null !== $description) {
            $sets[] = 'description = :description';
            $params['description'] = $description;
        }
        if (null !== $type) {
            $sets[] = 'type = :type';
            $params['type'] = $type->value;
        }
        if (null !== $acceptanceCriteria) {
            $sets[] = 'acceptance_criteria = :acceptance_criteria';
            $params['acceptance_criteria'] = $acceptanceCriteria;
        }

        $this->pdo->prepare(
            'UPDATE core.non_functional_requirements SET ' . implode(', ', $sets) . ' WHERE id = :id',
        )->execute($params);
    }
}
