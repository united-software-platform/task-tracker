<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Application\Repository\ProjectReadRepositoryInterface;
use App\Task\Domain\Model\Project;
use PDO;
use RuntimeException;

final readonly class PdoProjectRepository implements ProjectReadRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function findById(int $id): Project
    {
        $stmt = $this->pdo->prepare('SELECT id, code FROM core.projects WHERE id = :id');
        $stmt->execute(['id' => $id]);

        /** @var array{id: int, code: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $row) {
            throw new RuntimeException(sprintf('Project #%d not found', $id));
        }

        return new Project((int) $row['id'], $row['code']);
    }

    public function findByEpicId(int $epicId): Project
    {
        return $this->findByEntityId('epic', $epicId);
    }

    public function findByStoryId(int $storyId): Project
    {
        return $this->findByEntityId('story', $storyId);
    }

    private function findByEntityId(string $entityType, int $entityId): Project
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id, p.code
             FROM core.projects p
             JOIN core.project_entities pe ON pe.project_id = p.id
             JOIN core.entity_types et ON et.id = pe.entity_type_id AND et.type = :type
             WHERE pe.entity_id = :entity_id',
        );
        $stmt->execute(['type' => $entityType, 'entity_id' => $entityId]);

        /** @var array{id: int, code: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $row) {
            throw new RuntimeException(sprintf('%s #%d is not linked to any project', ucfirst($entityType), $entityId));
        }

        return new Project((int) $row['id'], $row['code']);
    }
}
