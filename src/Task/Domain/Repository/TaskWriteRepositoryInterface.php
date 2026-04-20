<?php

declare(strict_types=1);

namespace App\Task\Domain\Repository;

use App\Task\Domain\Model\Task;

interface TaskWriteRepositoryInterface
{
    public function create(Task $task): Task;

    public function update(Task $task): void;
}
