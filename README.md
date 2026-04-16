# REQ-CONTROL СТР — Централизованная система управления требованиями

[![PHPStan](https://github.com/united-software-platform/req-control/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/united-software-platform/req-control/actions/workflows/phpstan.yml)
[![PHP CS Fixer](https://github.com/united-software-platform/req-control/actions/workflows/php-cs-fixer.yml/badge.svg?branch=main)](https://github.com/united-software-platform/req-control/actions/workflows/php-cs-fixer.yml)

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
├── docker/
│   └── php/
│       └── Dockerfile           # PHP 8.3-cli-alpine + Composer
├── migrations/
│   ├── db.changelog-master.xml
│   └── sql/                     # Liquibase SQL-миграции
├── src/                         # Исходный код приложения (PSR-4: App\)
├── tests/
│   └── Unit/                    # Юнит-тесты
├── docs/
│   ├── database/
│   │   └── schema.sql           # Эталонная схема БД
│   ├── tasks/                   # Постановки задач
│   ├── schema.toon              # Компактное описание схемы
│   └── REQ Control system.jpg  # Диаграмма модели данных
├── data/                        # Тома БД и брокера (не в git)
├── docker-compose.yml
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
make code-setup       # Анализ + исправление стиля

make composer-install # composer install
make composer-update  # composer update
```

## Инструменты качества кода

| Инструмент    | Версия | Назначение                    |
|---------------|--------|-------------------------------|
| PHPStan       | ^2.1   | Статический анализ (level 8)  |
| PHP-CS-Fixer  | ^3.70  | Форматирование кода (PSR-12)  |
| PHPUnit       | ^12.1  | Тестирование                  |

## Модель данных

```
Epic
 └── Story
      └── Task  (status, readiness %)
```

Иерархия трёх уровней: эпик → стори → задача. Статусы задач хранятся в справочнике `core.statuses`. Состояние готовности эпика и стори агрегируется приложением из дочерних записей.

## Документация

- [Схема данных](docs/database/schema.sql)
- [Компактное описание схемы](docs/schema.toon)
- [Диаграмма модели](docs/REQ%20Control%20system.jpg)
