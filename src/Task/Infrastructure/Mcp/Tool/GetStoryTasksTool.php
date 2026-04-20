<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use App\Task\Application\Dto\TaskSummary;
use App\Task\Application\UseCase\GetStoryTasks\GetStoryTasksInput;
use App\Task\Application\UseCase\GetStoryTasks\GetStoryTasksUseCaseInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class GetStoryTasksTool
{
    public function __construct(
        private GetStoryTasksUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'get_story_tasks',
        description: 'Возвращает список задач стори: id, title, статус, readiness %.',
    )]
    public function __invoke(
        #[Schema(description: 'ID стори', minimum: 1)]
        int $storyId,
    ): CallToolResult {
        $output = $this->useCase->execute(new GetStoryTasksInput($storyId));

        return CallToolResult::success(
            content: [new TextContent(array_map(
                static fn (TaskSummary $t) => ['id' => $t->id, 'title' => $t->title, 'status' => $t->status, 'readiness' => $t->readiness],
                $output->tasks,
            ))],
            meta: ['count' => count($output->tasks)],
        );
    }
}
