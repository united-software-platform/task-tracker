<?php

declare(strict_types=1);

namespace App\Task\Domain\Repository;

use App\Task\Domain\Model\Story;

interface StoryWriteRepositoryInterface
{
    public function create(Story $story): Story;
}
