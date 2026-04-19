<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\CreateBusinessRequirement;

use App\Requirement\Domain\Model\BusinessRequirement;
use App\Requirement\Domain\Repository\BusinessRequirementRepositoryInterface;

final readonly class CreateBusinessRequirementUseCase implements CreateBusinessRequirementUseCaseInterface
{
    public function __construct(
        private BusinessRequirementRepositoryInterface $requirements,
    ) {}

    public function execute(CreateBusinessRequirementInput $input): CreateBusinessRequirementOutput
    {
        $id = $this->requirements->nextId();
        $requirement = new BusinessRequirement($id, 'BT-' . $id, $input->description);

        $this->requirements->create($requirement, $input->projectId);

        return new CreateBusinessRequirementOutput($requirement->id, $requirement->code);
    }
}
