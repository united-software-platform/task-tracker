<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\GetProjectFunctionalRequirements;

use App\Requirement\Domain\Repository\FunctionalRequirementReadRepositoryInterface;

final readonly class GetProjectFunctionalRequirementsUseCase implements GetProjectFunctionalRequirementsUseCaseInterface
{
    public function __construct(
        private FunctionalRequirementReadRepositoryInterface $requirements,
    ) {}

    public function execute(GetProjectFunctionalRequirementsInput $input): GetProjectFunctionalRequirementsOutput
    {
        return new GetProjectFunctionalRequirementsOutput(
            $this->requirements->listByProjectId($input->projectId),
        );
    }
}
