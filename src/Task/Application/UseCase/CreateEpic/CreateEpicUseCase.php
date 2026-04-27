<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateEpic;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Task\Application\Repository\ProjectReadRepositoryInterface;
use App\Task\Domain\Model\EntityTypes;
use App\Task\Domain\Model\Epic;
use App\Task\Domain\Model\ProjectEntity;
use App\Task\Domain\Repository\EpicWriteRepositoryInterface;
use App\Task\Domain\Repository\ProjectEntityWriteRepositoryInterface;

final readonly class CreateEpicUseCase implements CreateEpicUseCaseInterface
{
    public function __construct(
        private EpicWriteRepositoryInterface $epics,
        private ProjectReadRepositoryInterface $projects,
        private ProjectEntityWriteRepositoryInterface $projectEntities,
        private CodeGeneratorInterface $codeGenerator,
        private EntityTypes $entityTypes,
    ) {}

    public function execute(CreateEpicInput $input): CreateEpicOutput
    {
        $project = $this->projects->findById($input->projectId);
        $code = $this->codeGenerator->generate($this->entityTypes->epic);

        $epic = $this->epics->create(new Epic(0, $code, $input->title, $input->description));

        $this->projectEntities->link(new ProjectEntity($project->id, $epic->id, $this->entityTypes->epic));

        return new CreateEpicOutput($epic->id, $epic->code, $epic->title);
    }
}
