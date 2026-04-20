<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Persistence;

use App\Task\Domain\Model\Epic;
use App\Task\Domain\Repository\EpicWriteRepositoryInterface;
use PDO;

final readonly class PdoEpicWriteRepository implements EpicWriteRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function create(Epic $epic): Epic
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core.epics (code, title, description) VALUES (:code, :title, :description) RETURNING id',
        );
        $stmt->execute([
            'code' => $epic->code,
            'title' => $epic->title,
            'description' => $epic->description,
        ]);

        /** @var array{id: int} $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Epic((int) $row['id'], $epic->code, $epic->title, $epic->description);
    }
}
