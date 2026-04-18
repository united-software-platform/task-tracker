<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateEpic;

use App\Task\Domain\Repository\EpicRepositoryInterface;

final readonly class CreateEpicUseCase implements CreateEpicUseCaseInterface
{
    public function __construct(
        private EpicRepositoryInterface $epics,
    ) {}

    public function execute(CreateEpicInput $input): CreateEpicOutput
    {
        $epic = $this->epics->create($input->title, $input->description);

        return new CreateEpicOutput($epic->id, $epic->title);
    }
}
