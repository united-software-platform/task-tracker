<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Repository;

use App\Requirement\Domain\Model\NonFunctionalRequirement;
use App\Requirement\Domain\Model\NonFunctionalRequirementDetail;

interface NonFunctionalRequirementReadRepositoryInterface
{
    /** @return list<NonFunctionalRequirement> */
    public function listByProjectId(int $projectId): array;

    public function findById(int $id): NonFunctionalRequirementDetail;
}
