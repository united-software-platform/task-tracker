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

    public function create(NonFunctionalRequirement $requirement): NonFunctionalRequirement
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core.non_functional_requirements (code, type, description, acceptance_criteria)
             VALUES (:code, :type, :description, :acceptance_criteria)
             RETURNING id, code, type, description, acceptance_criteria',
        );
        $stmt->execute([
            'code' => $requirement->code,
            'type' => $requirement->type->value,
            'description' => $requirement->description,
            'acceptance_criteria' => $requirement->acceptanceCriteria,
        ]);

        /** @var array{id: int, code: string, type: string, description: string, acceptance_criteria: null|string} $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new NonFunctionalRequirement(
            (int) $row['id'],
            $row['code'],
            NonFunctionalRequirementType::from($row['type']),
            $row['description'],
            $row['acceptance_criteria'],
        );
    }

    public function update(NonFunctionalRequirement $requirement): void
    {
        $this->pdo->prepare(
            'UPDATE core.non_functional_requirements
             SET type = :type, description = :description, acceptance_criteria = :acceptance_criteria, updated_at = now()
             WHERE id = :id',
        )->execute([
            'id' => $requirement->id,
            'type' => $requirement->type->value,
            'description' => $requirement->description,
            'acceptance_criteria' => $requirement->acceptanceCriteria,
        ]);
    }
}
