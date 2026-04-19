<?php

declare(strict_types=1);

namespace App\Task\Domain\Model;

enum EntityType: string
{
    case Epic = 'epic';
    case Story = 'story';
    case Task = 'task';
}