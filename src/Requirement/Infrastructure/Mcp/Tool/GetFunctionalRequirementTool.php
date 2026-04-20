<?php

declare(strict_types=1);

namespace App\Requirement\Infrastructure\Mcp\Tool;

use App\Requirement\Application\UseCase\GetFunctionalRequirement\GetFunctionalRequirementInput;
use App\Requirement\Application\UseCase\GetFunctionalRequirement\GetFunctionalRequirementUseCaseInterface;
use App\Task\Domain\Model\TaskSummary;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class GetFunctionalRequirementTool
{
    public function __construct(
        private GetFunctionalRequirementUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'get_functional_requirement',
        description: 'Возвращает детали функционального требования: код FT-XXX, полное описание, связанные задачи проекта (ids, статусы), created_at, updated_at.',
    )]
    public function __invoke(
        #[Schema(description: 'ID функционального требования', minimum: 1)]
        int $requirementId,
    ): CallToolResult {
        $output = $this->useCase->execute(new GetFunctionalRequirementInput($requirementId));
        $r = $output->requirement;

        return CallToolResult::success(
            content: [new TextContent([
                'id' => $r->id,
                'code' => $r->code,
                'description' => $r->description,
                'created_at' => $r->createdAt,
                'updated_at' => $r->updatedAt,
                'tasks' => array_map(
                    static fn (TaskSummary $t) => ['id' => $t->id, 'status' => $t->status],
                    $output->tasks,
                ),
            ])],
        );
    }
}
