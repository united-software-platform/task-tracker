<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetEpics;

use App\Task\Application\Repository\EpicReadRepositoryInterface;

final readonly class GetEpicsUseCase implements GetEpicsUseCaseInterface
{
    public function __construct(
        private EpicReadRepositoryInterface $epics,
    ) {}

    public function execute(): GetEpicsOutput
    {
        return new GetEpicsOutput($this->epics->listAll());
    }
}
