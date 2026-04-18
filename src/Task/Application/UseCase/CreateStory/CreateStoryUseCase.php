<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateStory;

use App\Task\Domain\Repository\StoryRepositoryInterface;

final readonly class CreateStoryUseCase implements CreateStoryUseCaseInterface
{
    public function __construct(
        private StoryRepositoryInterface $stories,
    ) {}

    public function execute(CreateStoryInput $input): CreateStoryOutput
    {
        $story = $this->stories->create($input->epicId, $input->title, $input->description);

        return new CreateStoryOutput($story->id, $story->title);
    }
}
