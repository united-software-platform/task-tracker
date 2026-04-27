<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use App\Shared\Domain\ValueObject\EntityType;

interface CodeGeneratorInterface
{
    public function generate(EntityType $entityType): string;
}
