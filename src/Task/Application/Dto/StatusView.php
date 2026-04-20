<?php

declare(strict_types=1);

namespace App\Task\Application\Dto;

final readonly class StatusView
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}
