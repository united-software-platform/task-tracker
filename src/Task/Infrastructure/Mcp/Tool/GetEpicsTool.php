<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use App\Task\Application\Dto\EpicSummary;
use App\Task\Application\UseCase\GetEpics\GetEpicsUseCaseInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class GetEpicsTool
{
    public function __construct(
        private GetEpicsUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'get_epics',
        description: 'Возвращает список всех эпиков: id, title, количество сторей.',
    )]
    public function __invoke(): CallToolResult
    {
        $output = $this->useCase->execute();

        return CallToolResult::success(
            content: [new TextContent(array_map(
                static fn (EpicSummary $e) => ['id' => $e->id, 'title' => $e->title, 'story_count' => $e->storyCount],
                $output->epics,
            ))],
            meta: ['count' => count($output->epics)],
        );
    }
}
