#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Mcp\Tool\GetTaskStatusesTool;
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;

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

$server = Server::builder()
    ->setServerInfo(name: 'req-control', version: '1.0.0', description: 'REQ-CONTROL MCP Server')
    ->addTool(
        handler: new GetTaskStatusesTool($pdo),
        name: 'get_task_statuses',
        description: 'Возвращает список всех статусов задач из справочника core.statuses (id, name).',
    )
    ->build();

$server->run(new StdioTransport());
