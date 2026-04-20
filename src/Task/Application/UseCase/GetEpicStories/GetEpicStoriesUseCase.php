<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetEpicStories;

use App\Task\Application\Repository\StoryReadRepositoryInterface;

final readonly class GetEpicStoriesUseCase implements GetEpicStoriesUseCaseInterface
{
    public function __construct(
        private StoryReadRepositoryInterface $stories,
    ) {}

    public function execute(GetEpicStoriesInput $input): GetEpicStoriesOutput
    {
        return new GetEpicStoriesOutput($this->stories->listByEpicId($input->epicId));
    }
}
