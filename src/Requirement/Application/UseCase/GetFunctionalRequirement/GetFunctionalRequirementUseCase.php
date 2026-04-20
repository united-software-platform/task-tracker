<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\GetFunctionalRequirement;

use App\Requirement\Domain\Repository\FunctionalRequirementRepositoryInterface;
use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class GetFunctionalRequirementUseCase implements GetFunctionalRequirementUseCaseInterface
{
    public function __construct(
        private FunctionalRequirementRepositoryInterface $requirements,
        private TaskRepositoryInterface $tasks,
    ) {}

    public function execute(GetFunctionalRequirementInput $input): GetFunctionalRequirementOutput
    {
        $requirement = $this->requirements->findById($input->requirementId);
        $tasks = $this->tasks->listByProjectId($requirement->projectId);

        return new GetFunctionalRequirementOutput($requirement, $tasks);
    }
}
