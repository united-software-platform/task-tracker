<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use App\Task\Application\UseCase\CreateStory\CreateStoryInput;
use App\Task\Application\UseCase\CreateStory\CreateStoryUseCaseInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class CreateStoryTool
{
    public function __construct(
        private CreateStoryUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'create_story',
        description: 'Создаёт новую стори внутри эпика. Возвращает id и title созданной стори.',
    )]
    public function __invoke(
        #[Schema(description: 'ID эпика, к которому относится стори', minimum: 1)]
        int $epicId,
        #[Schema(description: 'Название стори', minLength: 1, maxLength: 200)]
        string $title,
        #[Schema(description: 'Описание стори (необязательно)')]
        ?string $description = null,
    ): CallToolResult {
        $output = $this->useCase->execute(new CreateStoryInput($epicId, $title, $description));

        return CallToolResult::success(
            content: [new TextContent(['id' => $output->id, 'title' => $output->title])],
        );
    }
}
