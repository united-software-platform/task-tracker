<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use App\Task\Application\Dto\StorySummary;
use App\Task\Application\UseCase\GetEpicStories\GetEpicStoriesInput;
use App\Task\Application\UseCase\GetEpicStories\GetEpicStoriesUseCaseInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class GetEpicStoriesTool
{
    public function __construct(
        private GetEpicStoriesUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'get_epic_stories',
        description: 'Возвращает список сторей эпика: id, title, средний % готовности.',
    )]
    public function __invoke(
        #[Schema(description: 'ID эпика', minimum: 1)]
        int $epicId,
    ): CallToolResult {
        $output = $this->useCase->execute(new GetEpicStoriesInput($epicId));

        return CallToolResult::success(
            content: [new TextContent(array_map(
                static fn (StorySummary $s) => ['id' => $s->id, 'title' => $s->title, 'avg_readiness' => $s->avgReadiness],
                $output->stories,
            ))],
            meta: ['count' => count($output->stories)],
        );
    }
}
