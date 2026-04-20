<?php

declare(strict_types=1);

namespace App\Task\Application\Repository;

use App\Task\Application\Dto\StatusView;

interface StatusReadRepositoryInterface
{
    /** @return list<StatusView> */
    public function listAll(): array;
}
