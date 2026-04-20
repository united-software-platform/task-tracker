<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateEpic;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Task\Application\Repository\ProjectReadRepositoryInterface;
use App\Task\Domain\Model\EntityType;
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
    ) {}

    public function execute(CreateEpicInput $input): CreateEpicOutput
    {
        $project = $this->projects->findById($input->projectId);
        $code = $this->codeGenerator->generate($project->code, EntityType::Epic->value);

        $epic = $this->epics->create(new Epic(0, $code, $input->title, $input->description));

        $this->projectEntities->link(new ProjectEntity($project->id, $epic->id, EntityType::Epic));

        return new CreateEpicOutput($epic->id, $epic->code, $epic->title);
    }
}
