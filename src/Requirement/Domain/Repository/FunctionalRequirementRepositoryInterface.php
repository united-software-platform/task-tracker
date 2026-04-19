<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Repository;

use App\Requirement\Domain\Model\FunctionalRequirement;
use App\Requirement\Domain\Model\FunctionalRequirementDetail;

interface FunctionalRequirementRepositoryInterface
{
    /** @return list<FunctionalRequirement> */
    public function listByProjectId(int $projectId): array;

    public function findById(int $id): FunctionalRequirementDetail;

    public function nextId(): int;

    public function create(FunctionalRequirement $requirement, int $projectId): void;

    public function update(int $id, string $description): void;
}
