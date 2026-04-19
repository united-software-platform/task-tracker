<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateTask;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Task\Domain\Model\EntityType;
use App\Task\Domain\Repository\ProjectRepositoryInterface;
use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class CreateTaskUseCase implements CreateTaskUseCaseInterface
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
        private ProjectRepositoryInterface $projects,
        private CodeGeneratorInterface $codeGenerator,
    ) {}

    public function execute(CreateTaskInput $input): CreateTaskOutput
    {
        $project = $this->projects->findByStoryId($input->storyId);
        $code = $this->codeGenerator->generate($project->code, EntityType::Task->value);

        $task = $this->tasks->create($project->id, $code, $input->storyId, $input->title, $input->description);

        return new CreateTaskOutput($task->id, $task->code, $task->title, $task->status);
    }
}
