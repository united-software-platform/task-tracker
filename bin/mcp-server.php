#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Task\Application\UseCase\CreateEpic\CreateEpicUseCase;
use App\Task\Application\UseCase\CreateStory\CreateStoryUseCase;
use App\Task\Application\UseCase\CreateTask\CreateTaskUseCase;
use App\Task\Application\UseCase\GetEpicStories\GetEpicStoriesUseCase;
use App\Task\Application\UseCase\GetEpics\GetEpicsUseCase;
use App\Task\Application\UseCase\GetStoryTasks\GetStoryTasksUseCase;
use App\Task\Application\UseCase\GetTask\GetTaskUseCase;
use App\Task\Application\UseCase\UpdateTask\UpdateTaskUseCase;
use App\Task\Infrastructure\Mcp\Tool\CreateEpicTool;
use App\Task\Infrastructure\Mcp\Tool\CreateStoryTool;
use App\Task\Infrastructure\Mcp\Tool\CreateTaskTool;
use App\Task\Infrastructure\Mcp\Tool\GetEpicStoriesTool;
use App\Task\Infrastructure\Mcp\Tool\GetEpicsTool;
use App\Task\Infrastructure\Mcp\Tool\GetStoryTasksTool;
use App\Task\Infrastructure\Mcp\Tool\GetTaskStatusesTool;
use App\Task\Infrastructure\Mcp\Tool\GetTaskTool;
use App\Task\Infrastructure\Mcp\Tool\UpdateTaskTool;
use App\Shared\Domain\Service\EntityCodeGenerator;
use App\Shared\Infrastructure\Persistence\PostgresCodeGenerator;
use App\Task\Infrastructure\Persistence\PdoEpicReadRepository;
use App\Task\Infrastructure\Persistence\PdoEpicWriteRepository;
use App\Task\Infrastructure\Persistence\PdoProjectEntityWriteRepository;
use App\Task\Infrastructure\Persistence\PdoProjectRepository;
use App\Task\Infrastructure\Persistence\PdoStoryReadRepository;
use App\Task\Infrastructure\Persistence\PdoStoryWriteRepository;
use App\Task\Infrastructure\Persistence\PdoTaskReadRepository;
use App\Task\Infrastructure\Persistence\PdoTaskWriteRepository;
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;

/** @var array{version: string} $composerJson */
$composerJson = json_decode((string) file_get_contents(__DIR__ . '/../composer.json'), true);
$appVersion = $composerJson['version'] ?? '0.0.0';

$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s',
    $_ENV['POSTGRES_HOST'] ?? getenv('POSTGRES_HOST') ?: 'postgres',
    $_ENV['POSTGRES_PORT'] ?? getenv('POSTGRES_PORT') ?: '5432',
    $_ENV['POSTGRES_DB']   ?? getenv('POSTGRES_DB')   ?: 'task_tracker',
);

$pdo = new PDO(
    $dsn,
    $_ENV['POSTGRES_USER']     ?? getenv('POSTGRES_USER')     ?: 'tracker',
    $_ENV['POSTGRES_PASSWORD'] ?? getenv('POSTGRES_PASSWORD') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$codeGenerator          = new PostgresCodeGenerator($pdo, new EntityCodeGenerator());
$projectRepository      = new PdoProjectRepository($pdo);
$projectEntityRepository = new PdoProjectEntityWriteRepository($pdo);

$epicWriteRepository  = new PdoEpicWriteRepository($pdo);
$epicReadRepository   = new PdoEpicReadRepository($pdo);
$storyWriteRepository = new PdoStoryWriteRepository($pdo);
$storyReadRepository  = new PdoStoryReadRepository($pdo);
$taskWriteRepository  = new PdoTaskWriteRepository($pdo);
$taskReadRepository   = new PdoTaskReadRepository($pdo);

$server = Server::builder()
    ->setServerInfo(name: 'req-control', version: $appVersion, description: 'REQ-CONTROL MCP Server')
    ->addTool(
        handler: new GetTaskStatusesTool($pdo)(...),
        name: 'get_task_statuses',
        description: 'Возвращает список всех статусов задач из справочника core.statuses (id, name).',
    )
    ->addTool(
        handler: new CreateEpicTool(new CreateEpicUseCase($epicWriteRepository, $projectRepository, $projectEntityRepository, $codeGenerator))(...),
        name: 'create_epic',
        description: 'Создаёт новый эпик. Возвращает id и title созданного эпика.',
    )
    ->addTool(
        handler: new CreateStoryTool(new CreateStoryUseCase($storyWriteRepository, $projectRepository, $projectEntityRepository, $codeGenerator))(...),
        name: 'create_story',
        description: 'Создаёт новую стори внутри эпика. Возвращает id и title созданной стори.',
    )
    ->addTool(
        handler: new CreateTaskTool(new CreateTaskUseCase($taskWriteRepository, $projectRepository, $projectEntityRepository, $codeGenerator))(...),
        name: 'create_task',
        description: 'Создаёт новую задачу внутри стори. Статус устанавливается «Новая» (1). Возвращает id, title и status.',
    )
    ->addTool(
        handler: new GetEpicsTool(new GetEpicsUseCase($epicReadRepository))(...),
        name: 'get_epics',
        description: 'Возвращает список всех эпиков: id, title, количество сторей.',
    )
    ->addTool(
        handler: new GetEpicStoriesTool(new GetEpicStoriesUseCase($storyReadRepository))(...),
        name: 'get_epic_stories',
        description: 'Возвращает список сторей эпика: id, title, средний % готовности.',
    )
    ->addTool(
        handler: new GetStoryTasksTool(new GetStoryTasksUseCase($taskReadRepository))(...),
        name: 'get_story_tasks',
        description: 'Возвращает список задач стори: id, title, статус, readiness %.',
    )
    ->addTool(
        handler: new GetTaskTool(new GetTaskUseCase($taskReadRepository))(...),
        name: 'get_task',
        description: 'Возвращает детали задачи: id, title, description, статус, readiness %, created_at, updated_at.',
    )
    ->addTool(
        handler: new UpdateTaskTool(new UpdateTaskUseCase($taskWriteRepository, $taskReadRepository))(...),
        name: 'update_task',
        description: 'Обновляет поля задачи: title, description, readiness (0–100), status из справочника. Передавай только изменяемые поля.',
    )
    ->build();

$server->run(new StdioTransport());
