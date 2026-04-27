<?php

declare(strict_types=1);

namespace App\Task\Domain\Model;

use App\Shared\Domain\ValueObject\EntityType;

final readonly class EntityTypes
{
    public EntityType $epic;
    public EntityType $story;
    public EntityType $task;

    public function __construct()
    {
        $this->epic = new EntityType('EPIC');
        $this->story = new EntityType('STRY');
        $this->task = new EntityType('TASK');
    }
}
