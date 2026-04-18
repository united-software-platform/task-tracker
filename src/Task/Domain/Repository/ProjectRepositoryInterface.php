<?php

declare(strict_types=1);

namespace App\Task\Domain\Repository;

use App\Task\Domain\Model\Project;

interface ProjectRepositoryInterface
{
    public function findById(int $id): Project;

    public function findByEpicId(int $epicId): Project;

    public function findByStoryId(int $storyId): Project;
}
