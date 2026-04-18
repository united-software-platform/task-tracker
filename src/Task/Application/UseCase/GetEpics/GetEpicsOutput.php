<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetEpics;

use App\Task\Domain\Model\EpicSummary;

final readonly class GetEpicsOutput
{
    public function __construct(
        /** @var list<EpicSummary> */
        public array $epics,
    ) {}
}
