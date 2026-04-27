<?php

declare(strict_types=1);

namespace App\Task\Application\UseCase\CreateTask;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Task\Application\Repository\ProjectReadRepositoryInterface;
use App\Task\Domain\Model\EntityTypes;
use App\Task\Domain\Model\ProjectEntity;
use App\Task\Domain\Model\Task;
use App\Task\Domain\Repository\ProjectEntityWriteRepositoryInterface;
use App\Task\Domain\Repository\TaskWriteRepositoryInterface;

final readonly class CreateTaskUseCase implements CreateTaskUseCaseInterface
{
    public function __construct(
        private TaskWriteRepositoryInterface $tasks,
        private ProjectReadRepositoryInterface $projects,
        private ProjectEntityWriteRepositoryInterface $projectEntities,
        private CodeGeneratorInterface $codeGenerator,
        private EntityTypes $entityTypes,
    ) {}

    public function execute(CreateTaskInput $input): CreateTaskOutput
    {
        $project = $this->projects->findByStoryId($input->storyId);
        $code = $this->codeGenerator->generate($this->entityTypes->task);

        $task = $this->tasks->create(new Task(
            id: 0,
            code: $code,
            projectId: $project->id,
            storyId: $input->storyId,
            title: $input->title,
            description: $input->description,
            status: Task::STATUS_NEW,
            readiness: 0,
            model: null,
        ));

        $this->projectEntities->link(new ProjectEntity($project->id, $task->id, $this->entityTypes->task));

        return new CreateTaskOutput($task->id, $task->code, $task->title, $task->status);
    }
}
