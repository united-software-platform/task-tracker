<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetEpicStories;

use App\Task\Domain\Model\StorySummary;

final readonly class GetEpicStoriesOutput
{
    public function __construct(
        /** @var list<StorySummary> */
        public array $stories,
    ) {}
}
