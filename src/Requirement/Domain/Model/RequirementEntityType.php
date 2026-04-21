<?php

declare(strict_types=1);

namespace App\Requirement\Domain\Model;

enum RequirementEntityType: string
{
    case BusinessRequirement = 'bt';
    case FunctionalRequirement = 'ft';
    case NonFunctionalRequirement = 'nft';
}
