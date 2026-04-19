<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateEpic;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Task\Domain\Model\EntityType;
use App\Task\Domain\Repository\EpicRepositoryInterface;
use App\Task\Domain\Repository\ProjectRepositoryInterface;

final readonly class CreateEpicUseCase implements CreateEpicUseCaseInterface
{
    public function __construct(
        private EpicRepositoryInterface $epics,
        private ProjectRepositoryInterface $projects,
        private CodeGeneratorInterface $codeGenerator,
    ) {}

    public function execute(CreateEpicInput $input): CreateEpicOutput
    {
        $project = $this->projects->findById($input->projectId);
        $code = $this->codeGenerator->generate($project->code, EntityType::Epic->value);

        $epic = $this->epics->create($project->id, $code, $input->title, $input->description);

        return new CreateEpicOutput($epic->id, $epic->code, $epic->title);
    }
}
