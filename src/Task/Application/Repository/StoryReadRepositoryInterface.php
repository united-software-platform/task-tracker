<?php

declare(strict_types=1);

namespace App\Task\Application\Repository;

use App\Task\Application\Dto\StorySummary;

interface StoryReadRepositoryInterface
{
    /** @return list<StorySummary> */
    public function listByEpicId(int $epicId): array;
}
