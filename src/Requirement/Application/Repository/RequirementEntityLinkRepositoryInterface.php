<?php

declare(strict_types=1);

namespace App\Requirement\Application\Repository;

use App\Shared\Domain\ValueObject\EntityType;

interface RequirementEntityLinkRepositoryInterface
{
    public function link(int $entityId, EntityType $entityType): void;
}
