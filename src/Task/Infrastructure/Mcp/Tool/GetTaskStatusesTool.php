<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Mcp\Tool;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;
use PDO;

final readonly class GetTaskStatusesTool
{
    public function __construct(
        private PDO $pdo,
    ) {}

    #[McpTool(
        name: 'get_task_statuses',
        description: 'Возвращает список всех статусов задач из справочника core.statuses (id, name).',
    )]
    public function __invoke(): CallToolResult
    {
        $stmt = $this->pdo->query('SELECT id, name FROM core.statuses ORDER BY id');

        if (false === $stmt) {
            return CallToolResult::error([
                new TextContent('Не удалось выполнить запрос к core.statuses'),
            ]);
        }

        /** @var list<array{id: int, name: string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return CallToolResult::success(
            content: [new TextContent($rows)],
            meta: ['count' => \count($rows)],
        );
    }
}
