<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Repository;

use App\Requirement\Domain\Model\FunctionalRequirement;

interface FunctionalRequirementWriteRepositoryInterface
{
    public function create(int $projectId, string $code, string $description): FunctionalRequirement;

    public function update(int $id, string $description): void;
}
