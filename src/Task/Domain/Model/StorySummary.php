<?php

declare(strict_types=1);

namespace App\Task\Domain\Model;

final readonly class StorySummary
{
    public function __construct(
        public int $id,
        public string $title,
        public int $avgReadiness,
    ) {}
}
