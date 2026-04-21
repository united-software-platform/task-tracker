<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Repository;

use App\Requirement\Domain\Model\NonFunctionalRequirement;
use App\Requirement\Domain\Model\NonFunctionalRequirementType;

interface NonFunctionalRequirementWriteRepositoryInterface
{
    public function create(int $projectId, string $code, NonFunctionalRequirementType $type, string $description, ?string $acceptanceCriteria): NonFunctionalRequirement;

    public function update(int $id, ?string $description, ?NonFunctionalRequirementType $type, ?string $acceptanceCriteria): void;
}
