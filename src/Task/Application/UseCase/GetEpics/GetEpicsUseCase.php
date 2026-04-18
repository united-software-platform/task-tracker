<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\GetEpics;

use App\Task\Domain\Repository\EpicRepositoryInterface;

final readonly class GetEpicsUseCase implements GetEpicsUseCaseInterface
{
    public function __construct(
        private EpicRepositoryInterface $epics,
    ) {}

    public function execute(): GetEpicsOutput
    {
        return new GetEpicsOutput($this->epics->listAll());
    }
}
