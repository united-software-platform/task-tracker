<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\GetFunctionalRequirement;

use App\Requirement\Domain\Model\FunctionalRequirementDetail;
use App\Task\Domain\Model\TaskSummary;

final readonly class GetFunctionalRequirementOutput
{
    public function __construct(
        public FunctionalRequirementDetail $requirement,
        /** @var list<TaskSummary> */
        public array $tasks,
    ) {}
}
