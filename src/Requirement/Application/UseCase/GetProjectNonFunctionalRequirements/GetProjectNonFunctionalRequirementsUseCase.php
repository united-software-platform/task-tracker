<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\GetProjectNonFunctionalRequirements;

use App\Requirement\Domain\Repository\NonFunctionalRequirementReadRepositoryInterface;

final readonly class GetProjectNonFunctionalRequirementsUseCase implements GetProjectNonFunctionalRequirementsUseCaseInterface
{
    public function __construct(
        private NonFunctionalRequirementReadRepositoryInterface $requirements,
    ) {}

    public function execute(GetProjectNonFunctionalRequirementsInput $input): GetProjectNonFunctionalRequirementsOutput
    {
        return new GetProjectNonFunctionalRequirementsOutput(
            $this->requirements->listByProjectId($input->projectId),
        );
    }
}
