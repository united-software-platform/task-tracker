<?php

declare(strict_types=1);

namespace App\Task\Domain\Repository;

use App\Task\Domain\Model\Epic;

interface EpicWriteRepositoryInterface
{
    public function create(Epic $epic): Epic;
}
