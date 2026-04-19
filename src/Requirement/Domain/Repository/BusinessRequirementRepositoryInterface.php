<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Repository;

use App\Requirement\Domain\Model\BusinessRequirement;
use App\Requirement\Domain\Model\BusinessRequirementDetail;

interface BusinessRequirementRepositoryInterface
{
    /** @return list<BusinessRequirement> */
    public function listByProjectId(int $projectId): array;

    public function findById(int $id): BusinessRequirementDetail;

    public function nextId(): int;

    public function create(BusinessRequirement $requirement, int $projectId): void;

    public function update(int $id, string $description): void;
}
