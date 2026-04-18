<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use App\Task\Application\UseCase\CreateTask\CreateTaskInput;
use App\Task\Application\UseCase\CreateTask\CreateTaskUseCaseInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class CreateTaskTool
{
    public function __construct(
        private CreateTaskUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'create_task',
        description: 'Создаёт новую задачу внутри стори. Статус устанавливается «Новая» (1). Возвращает id, title и status.',
    )]
    public function __invoke(
        #[Schema(description: 'ID стори, к которой относится задача', minimum: 1)]
        int $storyId,
        #[Schema(description: 'Название задачи', minLength: 1, maxLength: 200)]
        string $title,
        #[Schema(description: 'Описание задачи (необязательно)')]
        ?string $description = null,
    ): CallToolResult {
        $output = $this->useCase->execute(new CreateTaskInput($storyId, $title, $description));

        return CallToolResult::success(
            content: [new TextContent(['id' => $output->id, 'title' => $output->title, 'status' => $output->status])],
        );
    }
}
