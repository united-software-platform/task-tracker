<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateStory;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Task\Application\Repository\ProjectReadRepositoryInterface;
use App\Task\Domain\Model\EntityTypes;
use App\Task\Domain\Model\ProjectEntity;
use App\Task\Domain\Model\Story;
use App\Task\Domain\Repository\ProjectEntityWriteRepositoryInterface;
use App\Task\Domain\Repository\StoryWriteRepositoryInterface;

final readonly class CreateStoryUseCase implements CreateStoryUseCaseInterface
{
    public function __construct(
        private StoryWriteRepositoryInterface $stories,
        private ProjectReadRepositoryInterface $projects,
        private ProjectEntityWriteRepositoryInterface $projectEntities,
        private CodeGeneratorInterface $codeGenerator,
        private EntityTypes $entityTypes,
    ) {}

    public function execute(CreateStoryInput $input): CreateStoryOutput
    {
        $project = $this->projects->findByEpicId($input->epicId);
        $code = $this->codeGenerator->generate($this->entityTypes->story);

        $story = $this->stories->create(new Story(0, $code, $input->epicId, $input->title, $input->description));

        $this->projectEntities->link(new ProjectEntity($project->id, $story->id, $this->entityTypes->story));

        return new CreateStoryOutput($story->id, $story->code, $story->title);
    }
}
