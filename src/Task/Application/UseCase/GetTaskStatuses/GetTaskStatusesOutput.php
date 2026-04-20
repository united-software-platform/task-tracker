<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetTaskStatuses;

use App\Task\Application\Dto\StatusView;

final readonly class GetTaskStatusesOutput
{
    public function __construct(
        /** @var list<StatusView> */
        public array $statuses,
    ) {}
}
