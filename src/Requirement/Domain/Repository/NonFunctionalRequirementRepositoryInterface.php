<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Repository;

use App\Requirement\Domain\Model\NonFunctionalRequirement;
use App\Requirement\Domain\Model\NonFunctionalRequirementDetail;
use App\Requirement\Domain\Model\NonFunctionalRequirementType;

interface NonFunctionalRequirementRepositoryInterface
{
    /** @return list<NonFunctionalRequirement> */
    public function listByProjectId(int $projectId): array;

    public function findById(int $id): NonFunctionalRequirementDetail;

    public function nextId(): int;

    public function create(NonFunctionalRequirement $requirement, int $projectId, ?string $acceptanceCriteria): void;

    public function update(int $id, ?string $description, ?NonFunctionalRequirementType $type, ?string $acceptanceCriteria): void;
}
