<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Model;

final readonly class BusinessRequirementDetail
{
    public function __construct(
        public int $id,
        public string $code,
        public string $description,
        public int $projectId,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
