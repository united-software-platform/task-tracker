<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Repository;

use App\Requirement\Domain\Model\BusinessRequirement;

interface BusinessRequirementWriteRepositoryInterface
{
    public function create(int $projectId, string $code, string $description): BusinessRequirement;

    public function update(int $id, string $description): void;
}
