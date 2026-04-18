<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetEpicStories;

final readonly class GetEpicStoriesInput
{
    public function __construct(
        public int $epicId,
    ) {}
}
