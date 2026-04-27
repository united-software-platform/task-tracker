<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Model;

use App\Shared\Domain\ValueObject\EntityType;

final readonly class RequirementEntityTypes
{
    public EntityType $businessRequirement;
    public EntityType $functionalRequirement;
    public EntityType $nonFunctionalRequirement;

    public function __construct()
    {
        $this->businessRequirement = new EntityType('BTRQ');
        $this->functionalRequirement = new EntityType('FTRQ');
        $this->nonFunctionalRequirement = new EntityType('NFRQ');
    }
}
