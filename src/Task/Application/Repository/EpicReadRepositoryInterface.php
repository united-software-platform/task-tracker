<?php

declare(strict_types=1);

namespace App\Task\Application\Repository;

use App\Task\Application\Dto\EpicSummary;

interface EpicReadRepositoryInterface
{
    /** @return list<EpicSummary> */
    public function listAll(): array;
}
