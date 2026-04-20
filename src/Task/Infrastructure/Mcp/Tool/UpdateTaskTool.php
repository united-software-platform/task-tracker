<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use App\Task\Application\UseCase\UpdateTask\UpdateTaskInput;
use App\Task\Application\UseCase\UpdateTask\UpdateTaskUseCaseInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class UpdateTaskTool
{
    public function __construct(
        private UpdateTaskUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'update_task',
        description: 'Обновляет поля задачи: title, description, readiness (0–100), status из справочника, model. Передавай только изменяемые поля.',
    )]
    public function __invoke(
        #[Schema(description: 'ID задачи', minimum: 1)]
        int $taskId,
        #[Schema(description: 'Новое название задачи', minLength: 1, maxLength: 200)]
        ?string $title = null,
        #[Schema(description: 'Новое описание задачи')]
        ?string $description = null,
        #[Schema(description: 'Процент готовности (0–100)', minimum: 0, maximum: 100)]
        ?int $readiness = null,
        #[Schema(description: 'ID статуса из справочника', minimum: 1)]
        ?int $status = null,
        #[Schema(description: 'Модель/описание решения задачи')]
        ?string $model = null,
    ): CallToolResult {
        $this->useCase->execute(new UpdateTaskInput($taskId, $title, $description, $readiness, $status, $model));

        return CallToolResult::success(
            content: [new TextContent(['updated' => true, 'task_id' => $taskId])],
        );
    }
}
