<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateStory;

final readonly class CreateStoryOutput
{
    public function __construct(
        public int $id,
        public string $title,
    ) {}
}
