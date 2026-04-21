<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Repository;

use App\Requirement\Domain\Model\FunctionalRequirement;
use App\Requirement\Domain\Model\FunctionalRequirementDetail;

interface FunctionalRequirementReadRepositoryInterface
{
    /** @return list<FunctionalRequirement> */
    public function listByProjectId(int $projectId): array;

    public function findById(int $id): FunctionalRequirementDetail;
}
