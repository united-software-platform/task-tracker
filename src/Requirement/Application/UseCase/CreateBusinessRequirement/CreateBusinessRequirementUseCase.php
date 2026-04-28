<?php

declare(strict_types=1);

namespace App\Requirement\Application\UseCase\CreateBusinessRequirement;

use App\Requirement\Application\Repository\RequirementEntityLinkRepositoryInterface;
use App\Requirement\Domain\Model\BusinessRequirement;
use App\Requirement\Domain\Model\RequirementEntityTypes;
use App\Requirement\Domain\Repository\BusinessRequirementWriteRepositoryInterface;
use App\Shared\Application\Service\CodeGeneratorInterface;

final readonly class CreateBusinessRequirementUseCase implements CreateBusinessRequirementUseCaseInterface
{
    public function __construct(
        private BusinessRequirementWriteRepositoryInterface $requirements,
        private RequirementEntityLinkRepositoryInterface $entityLinks,
        private CodeGeneratorInterface $codeGenerator,
        private RequirementEntityTypes $entityTypes,
    ) {}

    public function execute(CreateBusinessRequirementInput $input): CreateBusinessRequirementOutput
    {
        $code = $this->codeGenerator->generate($this->entityTypes->businessRequirement);

        $requirement = $this->requirements->create(new BusinessRequirement(0, $code, $input->description));

        $this->entityLinks->link($requirement->id, $this->entityTypes->businessRequirement);

        return new CreateBusinessRequirementOutput($requirement->id, $requirement->code);
    }
}
