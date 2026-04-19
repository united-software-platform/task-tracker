<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\CreateNonFunctionalRequirement;

use App\Requirement\Domain\Model\NonFunctionalRequirement;
use App\Requirement\Domain\Repository\NonFunctionalRequirementRepositoryInterface;

final readonly class CreateNonFunctionalRequirementUseCase implements CreateNonFunctionalRequirementUseCaseInterface
{
    public function __construct(
        private NonFunctionalRequirementRepositoryInterface $requirements,
    ) {}

    public function execute(CreateNonFunctionalRequirementInput $input): CreateNonFunctionalRequirementOutput
    {
        $id = $this->requirements->nextId();
        $requirement = new NonFunctionalRequirement($id, 'NFT-' . $id, $input->type, $input->description);

        $this->requirements->create($requirement, $input->projectId, $input->acceptanceCriteria);

        return new CreateNonFunctionalRequirementOutput($requirement->id, $requirement->code);
    }
}
