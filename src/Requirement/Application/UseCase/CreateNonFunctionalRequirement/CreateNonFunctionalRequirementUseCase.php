<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\CreateNonFunctionalRequirement;

use App\Requirement\Application\Repository\RequirementEntityLinkRepositoryInterface;
use App\Requirement\Domain\Model\NonFunctionalRequirement;
use App\Requirement\Domain\Model\RequirementEntityTypes;
use App\Requirement\Domain\Repository\NonFunctionalRequirementWriteRepositoryInterface;
use App\Shared\Application\Service\CodeGeneratorInterface;

final readonly class CreateNonFunctionalRequirementUseCase implements CreateNonFunctionalRequirementUseCaseInterface
{
    public function __construct(
        private NonFunctionalRequirementWriteRepositoryInterface $requirements,
        private RequirementEntityLinkRepositoryInterface $entityLinks,
        private CodeGeneratorInterface $codeGenerator,
        private RequirementEntityTypes $entityTypes,
    ) {}

    public function execute(CreateNonFunctionalRequirementInput $input): CreateNonFunctionalRequirementOutput
    {
        $code = $this->codeGenerator->generate($this->entityTypes->nonFunctionalRequirement);

        $requirement = $this->requirements->create(new NonFunctionalRequirement(
            0,
            $code,
            $input->type,
            $input->description,
            $input->acceptanceCriteria,
        ));

        $this->entityLinks->link($requirement->id, $this->entityTypes->nonFunctionalRequirement);

        return new CreateNonFunctionalRequirementOutput($requirement->id, $requirement->code);
    }
}
