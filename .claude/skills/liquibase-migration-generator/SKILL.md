---
name: Liquibase Migration Generator
description: "Generates Liquibase .sql migration files for PostgreSQL. Use when user asks to create schema, create table, add column, or update column. Triggers: создай миграцию, создай схему, создай таблицу, добавь колонку, обнови колонку, migration."
allowed-tools:
  - Write
  - Glob
  - Grep
---

# Liquibase Migration Generator

Генератор Liquibase `.sql` файлов миграций для проекта task-trecker (PostgreSQL 16).

Навык только создаёт файлы. Запрещено запускать Liquibase, выполнять команды базы данных или применять миграции.

## Поддерживаемые действия

| action          | SQL-операция                                                       |
|-----------------|-------------------------------------------------------------------|
| `create schema` | `CREATE SCHEMA`                                                   |
| `create table`  | `CREATE TABLE`                                                    |
| `add column`    | `ALTER TABLE ... ADD COLUMN`                                      |
| `update column` | `ALTER TABLE ... ALTER COLUMN / RENAME / SET DEFAULT / ADD CONSTRAINT` |

## Алгоритм работы

1. Определить `action` из запроса пользователя.
2. Извлечь из запроса: схему (`schema`), таблицу (`table`), поля и их типы.
3. Проверить полноту данных:
   - Если данных недостаточно — задать **один** уточняющий вопрос и ждать ответа.
   - Если данных достаточно — перейти к шагу 4.
4. Glob: проверить существующие файлы в `migrations/sql/` на конфликт имён. Grep: проверить существующие `.sql` файлы на дублирование `changeset`-идентификатора.
5. Сформировать имя файла по шаблону.
6. Сгенерировать содержимое `.sql` файла.
7. Write файл в `migrations/sql/`.
8. Сообщить пользователю: имя файла и полный путь.

## Правила уточнения

**Запрещено** придумывать поля и их типы самостоятельно.

Когда задавать уточняющий вопрос:
- `create table` — поля не указаны: уточнить названия полей и их типы.
- `add column` — тип колонки не указан: уточнить тип.
- `update column` — не указано, что именно менять: уточнить характер изменения.

Всегда задавать **один** вопрос за раз.

## Автодобавляемые поля (только create table)

При `create table` добавлять автоматически, если явно не указаны в запросе:

- `id BIGSERIAL PRIMARY KEY` — **первым** в списке колонок.
- `created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()` — **в конце**.
- `updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()` — **в конце**.

## Шаблон имени файла

```
Version{timestamp}_{action}_{schema}_{table}.sql
```

- `{action}` — одно из: `create`, `add`, `update`.
- `{timestamp}` — текущая дата и время в формате `YYYYMMDDHHmmss`.
- Для `create schema` (без таблицы): `create-{schema}-v{timestamp}.sql`.

Примеры:
- `Version20260408143000_create_billing.sql`
- `Version20260408143000_create_billing_invoices.sql`
- `Version20260408143000_add_core_tasks.sql`
- `Version20260408143000_update_core_orders.sql`

## Путь сохранения

```
/home/llm-station/Project/Process/task-trecker/migrations/sql/
```

## Формат генерируемого .sql файла

```sql
--liquibase formatted sql

--changeset auto:{action}-{schema}-{table} labels:{action} comment:{описание на русском}
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 {precondition_query}

{SQL DDL}
```

### Precondition queries

| action          | precondition_query |
|-----------------|--------------------|
| `create schema` | `SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='{schema}'` |
| `create table`  | `SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{schema}' AND table_name='{table}'` |
| `add column`    | `SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='{schema}' AND table_name='{table}' AND column_name='{column}'` — повторить для каждой добавляемой колонки |
| `update column` | precondition **не добавляется** |

### Выравнивание SQL

Имена колонок и типы выравниваются по столбцам, как в `migrations/sql/001_init_schema.sql`:

```sql
CREATE TABLE billing.invoices (
    id          BIGSERIAL       PRIMARY KEY,
    amount      NUMERIC(12,2)   NOT NULL,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);
```

Правила выравнивания:
- Имя колонки — дополнить пробелами до ширины самого длинного имени в таблице + минимум 2 пробела.
- Тип данных — дополнить пробелами до ширины самого длинного типа + минимум 2 пробела.
- Ограничения (`NOT NULL`, `DEFAULT`, `REFERENCES`, `CHECK`) следуют за типом.

## Примеры

### Пример 1: create schema

Запрос: "Создай миграцию для новой схемы billing"

Файл: `create-billing-v20260408143000.sql`

```sql
--liquibase formatted sql

--changeset auto:create-billing labels:create comment:Создание схемы billing
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='billing'

CREATE SCHEMA billing;
```

### Пример 2: create table

Запрос: "Создай таблицу invoices в схеме billing, добавь поля amount NUMERIC(12,2) NOT NULL"

Файл: `create-billing-invoices-v20260408143000.sql`

```sql
--liquibase formatted sql

--changeset auto:create-billing-invoices labels:create comment:Создание таблицы invoices в схеме billing
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='billing' AND table_name='invoices'

CREATE TABLE billing.invoices (
    id          BIGSERIAL       PRIMARY KEY,
    amount      NUMERIC(12,2)   NOT NULL,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);
```

### Пример 3: add column

Запрос: "Добавь колонку archived BOOLEAN NOT NULL DEFAULT FALSE в таблицу tasks схемы core"

Файл: `add-core-tasks-v20260408143000.sql`

```sql
--liquibase formatted sql

--changeset auto:add-core-tasks labels:add comment:Добавление колонки archived в таблицу core.tasks
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='tasks' AND column_name='archived'

ALTER TABLE core.tasks
    ADD COLUMN archived BOOLEAN NOT NULL DEFAULT FALSE;
```

### Пример 4: update column

Запрос: "Обнови колонку status в таблице orders схемы core — изменить DEFAULT на 'PENDING'"

Файл: `update-core-orders-v20260408143000.sql`

```sql
--liquibase formatted sql

--changeset auto:update-core-orders labels:update comment:Изменение DEFAULT для колонки status в таблице core.orders

ALTER TABLE core.orders
    ALTER COLUMN status SET DEFAULT 'PENDING';
```

## Best Practices

Имена схем и таблиц писать в snake_case: `task_items`, `billing_accounts`, `user_profiles`.
Аббревиатуры не разворачивать: `id`, `url`, `uuid` — без изменений.

При объявлении колонок всегда явно указывать `NOT NULL` или `DEFAULT`, даже если тип это не требует.
Пример: `status TEXT NOT NULL DEFAULT 'ACTIVE'` — лучше, чем просто `status TEXT`.
Это защищает от неожиданного `NULL` при INSERT без явного значения и делает схему самодокументированной.

## Troubleshooting

### Пользователь не указал тип поля

Ситуация: запрос содержит только имена полей без типов, например "добавь поля name, email, phone".

Действие: задать уточняющий вопрос для каждой колонки за один раз:

> "Укажите типы данных для колонок:
> - `name` — какой тип? (например, `TEXT`, `VARCHAR(255)`)
> - `email` — какой тип?
> - `phone` — какой тип?"

Не генерировать файл до получения ответа. Не угадывать типы самостоятельно.

### Пользователь указал поля без ограничений (только имя и тип)

Ситуация: пользователь написал `amount NUMERIC(12,2)` без `NOT NULL` / `DEFAULT`.

Действие: использовать тип как есть — не добавлять ограничения автоматически, кроме автодобавляемых полей `id`, `created_at`, `updated_at` при `create table`.
Если ситуация неоднозначна — задать уточняющий вопрос: "Поле `amount` должно быть `NOT NULL`?"

### Конфликт имени файла (файл уже существует)

Ситуация: Glob находит файл с идентичным именем в `migrations/sql/`.

Это возможно при повторном запросе с той же секундой в timestamp.

Действие: сгенерировать timestamp заново, добавив 1 секунду к текущему значению.
Не перезаписывать существующий файл. Не использовать суффиксы вида `_2`, `_v2` — они нарушают шаблон именования.

### Для `update column` не указано, что именно менять

Ситуация: запрос "обнови колонку status в таблице orders" без деталей изменения.

Действие: задать вопрос:

> "Что нужно изменить в колонке `status`? Например:
> - переименовать → укажите новое имя
> - изменить тип → укажите новый тип
> - изменить DEFAULT → укажите новое значение
> - добавить ограничение CHECK → укажите условие"