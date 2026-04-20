<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use App\Task\Application\UseCase\GetTaskStatuses\GetTaskStatusesUseCaseInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class GetTaskStatusesTool
{
    public function __construct(
        private GetTaskStatusesUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'get_task_statuses',
        description: 'Возвращает список всех статусов задач из справочника core.statuses (id, name).',
    )]
    public function __invoke(): CallToolResult
    {
        $output = $this->useCase->execute();

        return CallToolResult::success(
            content: [new TextContent($output->statuses)],
            meta: ['count' => \count($output->statuses)],
        );
    }
}
