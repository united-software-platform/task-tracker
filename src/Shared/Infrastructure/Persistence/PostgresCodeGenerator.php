<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Application\Service\CodeGeneratorInterface;
use App\Shared\Domain\Service\EntityCodeGenerator;
use App\Shared\Domain\ValueObject\EntityType;
use PDO;

final readonly class PostgresCodeGenerator implements CodeGeneratorInterface
{
    public function __construct(
        private PDO $pdo,
        private EntityCodeGenerator $generator,
    ) {}

    public function generate(EntityType $entityType): string
    {
        $sequenceName = $this->buildSequenceName($entityType);

        /** @var int $nextVal */
        $nextVal = $this->pdo->query("SELECT nextval('{$sequenceName}')")->fetchColumn();

        return ($this->generator)($entityType, $nextVal);
    }

    private function buildSequenceName(EntityType $entityType): string
    {
        return sprintf('core.entity_code_%s_seq', strtolower($entityType->value));
    }
}
