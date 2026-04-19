<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateStory;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Task\Domain\Model\EntityType;
use App\Task\Domain\Repository\ProjectRepositoryInterface;
use App\Task\Domain\Repository\StoryRepositoryInterface;

final readonly class CreateStoryUseCase implements CreateStoryUseCaseInterface
{
    public function __construct(
        private StoryRepositoryInterface $stories,
        private ProjectRepositoryInterface $projects,
        private CodeGeneratorInterface $codeGenerator,
    ) {}

    public function execute(CreateStoryInput $input): CreateStoryOutput
    {
        $project = $this->projects->findByEpicId($input->epicId);
        $code = $this->codeGenerator->generate($project->code, EntityType::Story->value);

        $story = $this->stories->create($project->id, $code, $input->epicId, $input->title, $input->description);

        return new CreateStoryOutput($story->id, $story->code, $story->title);
    }
}
