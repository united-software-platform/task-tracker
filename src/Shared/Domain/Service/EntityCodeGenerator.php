<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\ValueObject\EntityType;

final class EntityCodeGenerator
{
    public function __invoke(EntityType $entityType, int $id): string
    {
        $formatted = $id < 1000 ? sprintf('%03d', $id) : (string) $id;

        return sprintf('%s-%s', $entityType->value, $formatted);
    }
}
