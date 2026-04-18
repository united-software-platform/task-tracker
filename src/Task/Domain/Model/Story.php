<?php

declare(strict_types=1);

namespace App\Task\Domain\Model;

final readonly class Story
{
    public function __construct(
        public int $id,
        public int $epicId,
        public string $title,
        public ?string $description,
    ) {}
}
