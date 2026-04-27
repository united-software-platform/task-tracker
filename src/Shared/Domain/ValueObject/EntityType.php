<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

final readonly class EntityType
{
    public function __construct(
        public string $value,
    ) {
        if (!preg_match('/^[A-Z]{4}$/', $value)) {
            throw new InvalidArgumentException(
                sprintf('EntityType must be exactly 4 uppercase Latin letters, "%s" given', $value),
            );
        }
    }
}
