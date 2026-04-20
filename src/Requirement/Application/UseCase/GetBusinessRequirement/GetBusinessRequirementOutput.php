<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\GetBusinessRequirement;

use App\Requirement\Domain\Model\BusinessRequirementDetail;
use App\Requirement\Domain\Model\FunctionalRequirement;

final readonly class GetBusinessRequirementOutput
{
    public function __construct(
        public BusinessRequirementDetail $requirement,
        /** @var list<FunctionalRequirement> */
        public array $functionalRequirements,
    ) {}
}
