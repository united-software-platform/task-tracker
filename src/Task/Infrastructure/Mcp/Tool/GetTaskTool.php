<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use App\Task\Application\UseCase\GetTask\GetTaskInput;
use App\Task\Application\UseCase\GetTask\GetTaskUseCaseInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class GetTaskTool
{
    public function __construct(
        private GetTaskUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'get_task',
        description: 'Возвращает детали задачи: id, title, description, статус, readiness %, model, created_at, updated_at.',
    )]
    public function __invoke(
        #[Schema(description: 'ID задачи', minimum: 1)]
        int $taskId,
    ): CallToolResult {
        $output = $this->useCase->execute(new GetTaskInput($taskId));
        $t = $output->task;

        return CallToolResult::success(
            content: [new TextContent([
                'id' => $t->id,
                'story_id' => $t->storyId,
                'title' => $t->title,
                'description' => $t->description,
                'status' => $t->status,
                'readiness' => $t->readiness,
                'model' => $t->model,
                'created_at' => $t->createdAt,
                'updated_at' => $t->updatedAt,
            ])],
        );
    }
}
