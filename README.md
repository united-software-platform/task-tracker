# Task Tracker — Система управления задачами

## Описание

Система для управления задачами с иерархической структурой на основе алгоритма Nested Sets.
Поддерживает вложенность задач (Эпик → Стори → Задача) и типизацию через справочник типов.

## Стек

| Компонент   | Технология              | Назначение                          |
|-------------|-------------------------|-------------------------------------|
| База данных | PostgreSQL 16           | Основное хранилище задач            |
| Миграции    | Liquibase 4.27          | Версионирование схемы БД            |
| Брокер      | RabbitMQ 3.13           | Обмен сообщениями между сервисами   |
| Окружение   | Docker / Docker Compose | Локальная разработка                |

## Структура проекта

```
task-tracker/
├── docker/
│   ├── docker-compose.yml       # Локальное окружение
│   └── .env.example
├── migrations/
│   ├── changelog/
│   │   └── db.changelog-master.xml
│   └── sql/
│       ├── Version20260408120000_create_core.sql
│       ├── Version20260408120001_create_core_tasks.sql
│       ├── Version20260408120002_add_core_tasks.sql
│       ├── Version20260408120003_update_core_tasks.sql
│       ├── Version20260408120004_create_core_types.sql
│       └── Version20260408120005_insert_core_types.sql
├── docs/
│   └── schema.md                # Описание схемы данных
└── README.md
```

## Быстрый старт

```bash
# Скопировать переменные окружения
cp docker/.env.example docker/.env

# Поднять окружение
docker compose -f docker/docker-compose.yml up -d

# Применить миграции
docker compose -f docker/docker-compose.yml --profile migrate run --rm liquibase
```

## Схема данных (кратко)

- `core.types` — справочник типов задач (Задача, Стори, Эпик)
- `core.tasks` — задачи с иерархией через Nested Sets
