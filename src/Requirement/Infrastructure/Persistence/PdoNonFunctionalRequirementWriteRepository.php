<?php

declare(strict_types=1);

namespace App\Requirement\Infrastructure\Persistence;

use App\Requirement\Domain\Model\NonFunctionalRequirement;
use App\Requirement\Domain\Model\NonFunctionalRequirementType;
use App\Requirement\Domain\Repository\NonFunctionalRequirementWriteRepositoryInterface;
use PDO;

final readonly class PdoNonFunctionalRequirementWriteRepository implements NonFunctionalRequirementWriteRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function create(int $projectId, string $code, NonFunctionalRequirementType $type, string $description, ?string $acceptanceCriteria): NonFunctionalRequirement
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core.non_functional_requirements (code, type, description, acceptance_criteria)
             VALUES (:code, :type, :description, :acceptance_criteria)
             RETURNING id, code, type, description',
        );
        $stmt->execute([
            'code' => $code,
            'type' => $type->value,
            'description' => $description,
            'acceptance_criteria' => $acceptanceCriteria,
        ]);

        /** @var array{id: int, code: string, type: string, description: string} $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->pdo->prepare(
            'INSERT INTO core.project_entities (project_id, entity_type_id, entity_id)
             SELECT :project_id, et.id, :entity_id FROM core.entity_types et WHERE et.type = \'nft\'',
        )->execute(['project_id' => $projectId, 'entity_id' => $row['id']]);

        return new NonFunctionalRequirement(
            (int) $row['id'],
            $row['code'],
            NonFunctionalRequirementType::from($row['type']),
            $row['description'],
        );
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
