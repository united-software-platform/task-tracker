<?php

declare(strict_types=1);

namespace App\Task\Domain\Repository;

use App\Task\Domain\Model\ProjectEntity;

interface ProjectEntityWriteRepositoryInterface
{
    public function link(ProjectEntity $entity): void;
}
