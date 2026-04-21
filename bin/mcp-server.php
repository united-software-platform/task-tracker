#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Requirement\Application\UseCase\CreateBusinessRequirement\CreateBusinessRequirementUseCase;
use App\Requirement\Application\UseCase\CreateFunctionalRequirement\CreateFunctionalRequirementUseCase;
use App\Requirement\Application\UseCase\CreateNonFunctionalRequirement\CreateNonFunctionalRequirementUseCase;
use App\Requirement\Application\UseCase\GetBusinessRequirement\GetBusinessRequirementUseCase;
use App\Requirement\Application\UseCase\GetFunctionalRequirement\GetFunctionalRequirementUseCase;
use App\Requirement\Application\UseCase\GetNonFunctionalRequirement\GetNonFunctionalRequirementUseCase;
use App\Requirement\Application\UseCase\GetProjectBusinessRequirements\GetProjectBusinessRequirementsUseCase;
use App\Requirement\Application\UseCase\GetProjectFunctionalRequirements\GetProjectFunctionalRequirementsUseCase;
use App\Requirement\Application\UseCase\GetProjectNonFunctionalRequirements\GetProjectNonFunctionalRequirementsUseCase;
use App\Requirement\Application\UseCase\UpdateBusinessRequirement\UpdateBusinessRequirementUseCase;
use App\Requirement\Application\UseCase\UpdateFunctionalRequirement\UpdateFunctionalRequirementUseCase;
use App\Requirement\Application\UseCase\UpdateNonFunctionalRequirement\UpdateNonFunctionalRequirementUseCase;
use App\Requirement\Infrastructure\Mcp\Tool\CreateBusinessRequirementTool;
use App\Requirement\Infrastructure\Mcp\Tool\CreateFunctionalRequirementTool;
use App\Requirement\Infrastructure\Mcp\Tool\CreateNonFunctionalRequirementTool;
use App\Requirement\Infrastructure\Mcp\Tool\GetBusinessRequirementTool;
use App\Requirement\Infrastructure\Mcp\Tool\GetFunctionalRequirementTool;
use App\Requirement\Infrastructure\Mcp\Tool\GetNonFunctionalRequirementTool;
use App\Requirement\Infrastructure\Mcp\Tool\GetProjectBusinessRequirementsTool;
use App\Requirement\Infrastructure\Mcp\Tool\GetProjectFunctionalRequirementsTool;
use App\Requirement\Infrastructure\Mcp\Tool\GetProjectNonFunctionalRequirementsTool;
use App\Requirement\Infrastructure\Mcp\Tool\UpdateBusinessRequirementTool;
use App\Requirement\Infrastructure\Mcp\Tool\UpdateFunctionalRequirementTool;
use App\Requirement\Infrastructure\Mcp\Tool\UpdateNonFunctionalRequirementTool;
use App\Requirement\Infrastructure\Persistence\PdoBusinessRequirementReadRepository;
use App\Requirement\Infrastructure\Persistence\PdoBusinessRequirementWriteRepository;
use App\Requirement\Infrastructure\Persistence\PdoFunctionalRequirementReadRepository;
use App\Requirement\Infrastructure\Persistence\PdoFunctionalRequirementWriteRepository;
use App\Requirement\Infrastructure\Persistence\PdoNonFunctionalRequirementReadRepository;
use App\Requirement\Infrastructure\Persistence\PdoNonFunctionalRequirementWriteRepository;
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
use App\Task\Application\UseCase\GetTaskStatuses\GetTaskStatusesUseCase;
use App\Task\Infrastructure\Persistence\PdoStatusReadRepository;
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

$ftReadRepository   = new PdoFunctionalRequirementReadRepository($pdo);
$ftWriteRepository  = new PdoFunctionalRequirementWriteRepository($pdo);
$btReadRepository   = new PdoBusinessRequirementReadRepository($pdo);
$btWriteRepository  = new PdoBusinessRequirementWriteRepository($pdo);
$nftReadRepository  = new PdoNonFunctionalRequirementReadRepository($pdo);
$nftWriteRepository = new PdoNonFunctionalRequirementWriteRepository($pdo);
$epicWriteRepository  = new PdoEpicWriteRepository($pdo);
$epicReadRepository   = new PdoEpicReadRepository($pdo);
$storyWriteRepository = new PdoStoryWriteRepository($pdo);
$storyReadRepository  = new PdoStoryReadRepository($pdo);
$taskWriteRepository  = new PdoTaskWriteRepository($pdo);
$taskReadRepository   = new PdoTaskReadRepository($pdo);
$statusReadRepository = new PdoStatusReadRepository($pdo);

$server = Server::builder()
    ->setServerInfo(name: 'req-control', version: $appVersion, description: 'REQ-CONTROL MCP Server')
    ->addTool(
        handler: new GetTaskStatusesTool(new GetTaskStatusesUseCase($statusReadRepository))(...),
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
    ->addTool(
        handler: new GetProjectFunctionalRequirementsTool(new GetProjectFunctionalRequirementsUseCase($ftReadRepository))(...),
        name: 'get_project_functional_requirements',
        description: 'Возвращает плоский список функциональных требований (ФТ) проекта: id, код FT-XXX, краткое описание.',
    )
    ->addTool(
        handler: new GetFunctionalRequirementTool(new GetFunctionalRequirementUseCase($ftReadRepository, $taskReadRepository))(...),
        name: 'get_functional_requirement',
        description: 'Возвращает детали функционального требования: код FT-XXX, полное описание, связанные задачи проекта (ids, статусы), created_at, updated_at.',
    )
    ->addTool(
        handler: new CreateFunctionalRequirementTool(new CreateFunctionalRequirementUseCase($ftWriteRepository, $projectRepository, $codeGenerator))(...),
        name: 'create_functional_requirement',
        description: 'Создаёт функциональное требование (ФТ) и привязывает его к проекту. Возвращает id и код FT-XXX.',
    )
    ->addTool(
        handler: new UpdateFunctionalRequirementTool(new UpdateFunctionalRequirementUseCase($ftWriteRepository))(...),
        name: 'update_functional_requirement',
        description: 'Обновляет функциональное требование (ФТ). Обновляет поле description и updated_at.',
    )
    ->addTool(
        handler: new GetProjectBusinessRequirementsTool(new GetProjectBusinessRequirementsUseCase($btReadRepository))(...),
        name: 'get_project_business_requirements',
        description: 'Возвращает плоский список бизнес-требований (БТ) проекта: id, код BT-XXX, краткое описание.',
    )
    ->addTool(
        handler: new GetBusinessRequirementTool(new GetBusinessRequirementUseCase($btReadRepository, $ftReadRepository))(...),
        name: 'get_business_requirement',
        description: 'Возвращает детали бизнес-требования: код BT-XXX, полное описание, связанные ФТ проекта (если есть), created_at, updated_at.',
    )
    ->addTool(
        handler: new CreateBusinessRequirementTool(new CreateBusinessRequirementUseCase($btWriteRepository, $projectRepository, $codeGenerator))(...),
        name: 'create_business_requirement',
        description: 'Создаёт бизнес-требование (БТ) и привязывает его к проекту. Возвращает id и код BT-XXX.',
    )
    ->addTool(
        handler: new UpdateBusinessRequirementTool(new UpdateBusinessRequirementUseCase($btWriteRepository))(...),
        name: 'update_business_requirement',
        description: 'Обновляет бизнес-требование (БТ). Обновляет поле description и updated_at.',
    )
    ->addTool(
        handler: new GetProjectNonFunctionalRequirementsTool(new GetProjectNonFunctionalRequirementsUseCase($nftReadRepository))(...),
        name: 'get_project_non_functional_requirements',
        description: 'Возвращает список нефункциональных требований (НФТ) проекта: id, код NFT-XXX, тип, краткое описание.',
    )
    ->addTool(
        handler: new GetNonFunctionalRequirementTool(new GetNonFunctionalRequirementUseCase($nftReadRepository))(...),
        name: 'get_non_functional_requirement',
        description: 'Возвращает детали нефункционального требования: код NFT-XXX, тип, полное описание, критерий приёмки, created_at, updated_at.',
    )
    ->addTool(
        handler: new CreateNonFunctionalRequirementTool(new CreateNonFunctionalRequirementUseCase($nftWriteRepository, $projectRepository, $codeGenerator))(...),
        name: 'create_non_functional_requirement',
        description: 'Создаёт нефункциональное требование (НФТ) и привязывает его к проекту. Возвращает id и код NFT-XXX.',
    )
    ->addTool(
        handler: new UpdateNonFunctionalRequirementTool(new UpdateNonFunctionalRequirementUseCase($nftWriteRepository))(...),
        name: 'update_non_functional_requirement',
        description: 'Обновляет нефункциональное требование (НФТ). Передавай только изменяемые поля: description, type, acceptanceCriteria.',
    )
    ->build();

$server->run(new StdioTransport());
