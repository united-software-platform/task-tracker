<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\CreateFunctionalRequirement;

use App\Requirement\Domain\Model\FunctionalRequirement;
use App\Requirement\Domain\Repository\FunctionalRequirementRepositoryInterface;

final readonly class CreateFunctionalRequirementUseCase implements CreateFunctionalRequirementUseCaseInterface
{
    public function __construct(
        private FunctionalRequirementRepositoryInterface $requirements,
    ) {}

    public function execute(CreateFunctionalRequirementInput $input): CreateFunctionalRequirementOutput
    {
        $id = $this->requirements->nextId();
        $requirement = new FunctionalRequirement($id, 'FT-' . $id, $input->description);

        $this->requirements->create($requirement, $input->projectId);

        return new CreateFunctionalRequirementOutput($requirement->id, $requirement->code);
    }
}
