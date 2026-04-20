<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateStory;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Task\Application\Repository\ProjectReadRepositoryInterface;
use App\Task\Domain\Model\EntityType;
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
    ) {}

    public function execute(CreateStoryInput $input): CreateStoryOutput
    {
        $project = $this->projects->findByEpicId($input->epicId);
        $code = $this->codeGenerator->generate($project->code, EntityType::Story->value);

        $story = $this->stories->create(new Story(0, $code, $input->epicId, $input->title, $input->description));

        $this->projectEntities->link(new ProjectEntity($project->id, $story->id, EntityType::Story));

        return new CreateStoryOutput($story->id, $story->code, $story->title);
    }
}
