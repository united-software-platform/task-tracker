<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateStory;

final readonly class CreateStoryInput
{
    public function __construct(
        public int $epicId,
        public string $title,
        public ?string $description,
    ) {}
}
