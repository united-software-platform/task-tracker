<?php

declare(strict_types=1);

namespace App\Requirement\Infrastructure\Mcp\Tool;

use App\Requirement\Application\UseCase\GetBusinessRequirement\GetBusinessRequirementInput;
use App\Requirement\Application\UseCase\GetBusinessRequirement\GetBusinessRequirementUseCaseInterface;
use App\Requirement\Domain\Model\FunctionalRequirement;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;

final readonly class GetBusinessRequirementTool
{
    public function __construct(
        private GetBusinessRequirementUseCaseInterface $useCase,
    ) {}

    #[McpTool(
        name: 'get_business_requirement',
        description: 'Возвращает детали бизнес-требования: код BT-XXX, полное описание, связанные ФТ проекта (если есть), created_at, updated_at.',
    )]
    public function __invoke(
        #[Schema(description: 'ID бизнес-требования', minimum: 1)]
        int $requirementId,
    ): CallToolResult {
        $output = $this->useCase->execute(new GetBusinessRequirementInput($requirementId));
        $r = $output->requirement;

        return CallToolResult::success(
            content: [new TextContent([
                'id' => $r->id,
                'code' => $r->code,
                'description' => $r->description,
                'created_at' => $r->createdAt,
                'updated_at' => $r->updatedAt,
                'functional_requirements' => array_map(
                    static fn (FunctionalRequirement $ft) => ['id' => $ft->id, 'code' => $ft->code, 'description' => $ft->description],
                    $output->functionalRequirements,
                ),
            ])],
        );
    }
}
