<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Domain\Model\Story;
use App\Task\Domain\Repository\StoryWriteRepositoryInterface;
use PDO;

final readonly class PdoStoryWriteRepository implements StoryWriteRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function create(Story $story): Story
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core.stories (code, epic_id, title, description) VALUES (:code, :epic_id, :title, :description) RETURNING id',
        );
        $stmt->execute([
            'code' => $story->code,
            'epic_id' => $story->epicId,
            'title' => $story->title,
            'description' => $story->description,
        ]);

        /** @var array{id: int} $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Story((int) $row['id'], $story->code, $story->epicId, $story->title, $story->description);
    }
}
