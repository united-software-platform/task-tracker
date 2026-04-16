# REQ-CONTROL СТР — Централизованная система управления требованиями

[![PHPStan](https://github.com/united-software-platform/req-control/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/united-software-platform/req-control/actions/workflows/phpstan.yml)
[![PHP CS Fixer](https://github.com/united-software-platform/req-control/actions/workflows/php-cs-fixer.yml/badge.svg?branch=main)](https://github.com/united-software-platform/req-control/actions/workflows/php-cs-fixer.yml)
[![Deptrac](https://github.com/united-software-platform/req-control/actions/workflows/deptrac.yml/badge.svg?branch=main)](https://github.com/united-software-platform/req-control/actions/workflows/deptrac.yml)

## Назначение

REQ-CONTROL СТР — централизованная система, обеспечивающая:

- **Регистрацию и формализацию требований** — единая точка фиксации всех требований проекта
- **Управление жизненным циклом требований** — от первичной регистрации до закрытия или отклонения
- **Трассируемость** — сквозная связь по цепочке: требование → задача → реализация → тест → результат
- **Контроль соответствия** — верификация результата относительно исходного требования
- **Фиксацию отклонений** — учёт незакрытых требований и выявленных несоответствий

## Стек

| Компонент       | Технология              | Назначение                                  |
|-----------------|-------------------------|---------------------------------------------|
| Backend         | PHP 8.4                 | Бизнес-логика приложения                    |
| База данных     | PostgreSQL 16           | Хранилище требований и связанных сущностей  |
| Миграции        | Liquibase 4.27          | Версионирование схемы БД                    |
| Брокер          | RabbitMQ 3.13           | Обмен событиями между сервисами             |
| Окружение       | Docker / Docker Compose | Локальная разработка                        |
| Зависимости     | Composer 2              | Управление PHP-пакетами                     |

## Структура проекта

```
req-control/
├── .github/workflows/           # CI: PHPStan, PHP-CS-Fixer, Deptrac
├── .claude/rules/               # Правила архитектуры для Claude Code
├── bin/
│   ├── console                  # Symfony Console
│   └── mcp-server.php           # MCP-сервер (stdio transport)
├── docker/
│   └── php/
│       └── Dockerfile           # PHP 8.3-cli-alpine + Composer
├── migrations/
│   ├── db.changelog-master.xml
│   └── sql/                     # Liquibase SQL-миграции
├── src/                         # Исходный код (PSR-4: App\)
│   ├── {Module}/                # Доменный модуль (Module-first)
│   │   ├── Domain/              # Entities, Value Objects, Repository interfaces
│   │   ├── Application/         # Use Cases, Input/Output DTO
│   │   └── Infrastructure/      # Doctrine, HTTP, MCP-инструменты
│   ├── Controller/              # Symfony HTTP-контроллеры
│   └── Kernel.php
├── tests/
│   └── Unit/                    # Юнит-тесты
├── docs/
│   ├── database/
│   │   └── schema.sql           # Эталонная схема БД
│   ├── tasks/                   # Постановки задач
│   ├── schema.toon              # Компактное описание схемы
│   └── REQ Control system.jpg   # Диаграмма модели данных
├── data/                        # Тома БД и брокера (не в git)
├── docker-compose.yml
├── deptrac.php                  # Конфиг контроля архитектурных зависимостей
├── .env.example
├── Makefile
├── composer.json
├── phpstan.neon
├── .php-cs-fixer.php
└── phpunit.xml
```

## Быстрый старт

```bash
make init
```

Команда выполняет:
1. Создаёт директории `var/`, `data/`
2. Копирует `.env.example` → `.env`
3. Поднимает контейнеры
4. Устанавливает зависимости Composer

### Применить миграции БД

```bash
make migrate
```

## Команды разработки

```bash
make up               # Запустить контейнеры
make down             # Остановить контейнеры
make restart          # Перезапустить контейнеры
make shell            # Войти в PHP-контейнер

make migrate          # Применить миграции БД (Liquibase)

make test             # Запустить тесты (PHPUnit)
make test-unit        # Только юнит-тесты
make analyze-code     # Статический анализ (PHPStan, уровень 8)
make check-style      # Проверить стиль кода (PHP-CS-Fixer, dry-run)
make fix-style        # Исправить стиль кода
make check-arch       # Контроль архитектурных зависимостей (Deptrac)
make code-setup       # Анализ + исправление стиля + проверка архитектуры

make composer-install # composer install
make composer-update  # composer update
```

## Инструменты качества кода

| Инструмент    | Версия | Назначение                                      |
|---------------|--------|-------------------------------------------------|
| PHPStan       | ^2.1   | Статический анализ (level 6)                    |
| PHP-CS-Fixer  | ^3.70  | Форматирование кода (PSR-12)                    |
| PHPUnit       | ^12.1  | Тестирование                                    |
| Deptrac       | ^2.0   | Контроль архитектурных зависимостей (Module-first) |

## Модель данных

```
Epic
 └── Story
      └── Task  (status → core.statuses, readiness %)
```

Иерархия трёх уровней: эпик → стори → задача. Статусы задач хранятся в справочнике `core.statuses`. Состояние готовности эпика и стори агрегируется приложением из дочерних записей.

## MCP-сервер

Приложение предоставляет [MCP](https://modelcontextprotocol.io)-сервер для интеграции с AI-инструментами (Claude Code и другими):

```bash
php bin/mcp-server.php
```

| Инструмент          | Описание                                              |
|---------------------|-------------------------------------------------------|
| `get_task_statuses` | Возвращает все статусы задач из `core.statuses`       |

Сервер использует stdio transport и читает параметры подключения к БД из переменных окружения (`POSTGRES_HOST`, `POSTGRES_PORT`, `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD`).

## Claude Code Skills

Проектные навыки для Claude Code расположены в `.claude/skills/`.

| Навык | Назначение | Когда использовать |
|---|---|---|
| `db-schema-keeper` | Протокол работы со схемой БД | Миграции, изменение/просмотр таблиц, колонок, индексов |
| `liquibase-migration-generator` | Генерация Liquibase SQL-миграций | Создание схем, таблиц, добавление/обновление колонок |
| `sql-pro` | Оптимизация PostgreSQL | Сложные запросы, индексирование, проблемы производительности |
| `architect-reviewer` | Ревью архитектурных решений | Оценка паттернов, выбор технологий, макро-архитектура |
| `business-analyst` | Анализ бизнес-процессов | Сбор требований, выявление улучшений, работа со стейкхолдерами |

## Документация

- [Схема данных](docs/database/schema.sql)
- [Компактное описание схемы](docs/schema.toon)
- [Диаграмма модели](docs/REQ%20Control%20system.jpg)
