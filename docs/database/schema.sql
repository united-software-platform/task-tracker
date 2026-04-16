-- =============================================================================
-- req-control · Database Schema
-- Engine: PostgreSQL 16   Schema: core
-- Version: 1.0.0  (2026-04-16)
-- =============================================================================
--
-- Модель данных: три сущности рабочего трекера — Epic, Story, Task.
-- Иерархия: Epic → Story → Task (три отдельных таблицы, связи через FK).
-- Состояние готовности Epic и Story агрегируется приложением из дочерних
-- записей и не хранится в БД.
-- Статусы задач вынесены в справочник core.statuses.
-- =============================================================================


-- -----------------------------------------------------------------------------
-- Schema
-- -----------------------------------------------------------------------------

CREATE SCHEMA IF NOT EXISTS core;


-- -----------------------------------------------------------------------------
-- TABLE core.statuses
-- Справочник статусов задач.
-- -----------------------------------------------------------------------------

CREATE TABLE core.statuses (
    id         SMALLINT        PRIMARY KEY,
    name       VARCHAR(100)    NOT NULL,
    created_at TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLE core.epics
-- Верхний уровень иерархии. Состояние готовности — агрегат из сторей.
-- -----------------------------------------------------------------------------

CREATE TABLE core.epics (
    id          BIGSERIAL       PRIMARY KEY,
    title       VARCHAR(200)    NOT NULL,
    description TEXT,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLE core.stories
-- Средний уровень иерархии. Состояние готовности — агрегат из задач.
-- -----------------------------------------------------------------------------

CREATE TABLE core.stories (
    id          BIGSERIAL       PRIMARY KEY,
    epic_id     BIGINT          NOT NULL,
    title       VARCHAR(200)    NOT NULL,
    description TEXT,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_stories_epic_id
        FOREIGN KEY (epic_id)
            REFERENCES core.epics(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE INDEX idx_core_stories_epic_id ON core.stories (epic_id);


-- -----------------------------------------------------------------------------
-- TABLE core.tasks
-- Нижний уровень иерархии. Имеет явный статус и процент готовности.
-- -----------------------------------------------------------------------------

CREATE TABLE core.tasks (
    id          BIGSERIAL       PRIMARY KEY,
    story_id    BIGINT          NOT NULL,
    title       VARCHAR(200)    NOT NULL,
    description TEXT,
    status      SMALLINT        NOT NULL,                         -- FK на core.statuses.id
    readiness   SMALLINT        NOT NULL DEFAULT 0
                                CHECK (readiness >= 0 AND readiness <= 100),  -- готовность, %
    blocked_by  BIGINT          NULL,                             -- задача-блокировщик; NULL = не заблокирована
    assignee    VARCHAR(200)    NULL,                             -- имя/логин исполнителя; NULL = нет исполнителя
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_tasks_story_id
        FOREIGN KEY (story_id)
            REFERENCES core.stories(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,

    CONSTRAINT fk_tasks_status
        FOREIGN KEY (status)
            REFERENCES core.statuses(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,

    CONSTRAINT fk_tasks_blocked_by
        FOREIGN KEY (blocked_by)
            REFERENCES core.tasks(id)
            ON DELETE SET NULL
);

CREATE INDEX idx_core_tasks_story_id  ON core.tasks (story_id);
CREATE INDEX idx_core_tasks_status    ON core.tasks (status);
CREATE INDEX idx_core_tasks_blocked_by ON core.tasks (blocked_by);


-- -----------------------------------------------------------------------------
-- TABLE core.functional_requirements
-- Функциональные требования к системе.
-- -----------------------------------------------------------------------------

CREATE TABLE core.functional_requirements (
    id          BIGSERIAL       PRIMARY KEY,
    code        VARCHAR(20)     NOT NULL UNIQUE,    -- пример: FT-001
    description TEXT            NOT NULL,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLE core.business_requirements
-- Бизнес-требования к системе.
-- -----------------------------------------------------------------------------

CREATE TABLE core.business_requirements (
    id          BIGSERIAL       PRIMARY KEY,
    code        VARCHAR(20)     NOT NULL UNIQUE,    -- пример: BT-001
    description TEXT            NOT NULL,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLE core.projects
-- Проекты.
-- -----------------------------------------------------------------------------

CREATE TABLE core.projects (
    id          BIGSERIAL       PRIMARY KEY,
    code        VARCHAR(20)     NOT NULL UNIQUE,    -- пример: PRJ-001
    description TEXT            NOT NULL,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLE core.entity_types
-- Справочник типов сущностей системы.
-- -----------------------------------------------------------------------------

CREATE TABLE core.entity_types (
    id         BIGSERIAL       PRIMARY KEY,
    type       VARCHAR(50)     NOT NULL UNIQUE,
    created_at TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLE core.project_entities
-- Таблица связи проекта с сущностями (полиморфная: entity_type_id + entity_id).
-- -----------------------------------------------------------------------------

CREATE TABLE core.project_entities (
    id             BIGSERIAL       PRIMARY KEY,
    project_id     BIGINT          NOT NULL,
    entity_type_id BIGINT          NOT NULL,
    entity_id      BIGINT          NOT NULL,               -- id сущности в соответствующей таблице
    created_at     TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at     TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_project_entities_project_id
        FOREIGN KEY (project_id)
            REFERENCES core.projects(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,

    CONSTRAINT fk_project_entities_entity_type_id
        FOREIGN KEY (entity_type_id)
            REFERENCES core.entity_types(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE INDEX idx_core_project_entities_project_id     ON core.project_entities (project_id);
CREATE INDEX idx_core_project_entities_entity_type_id ON core.project_entities (entity_type_id);
CREATE INDEX idx_core_project_entities_entity_id      ON core.project_entities (entity_id);


-- -----------------------------------------------------------------------------
-- SEED core.statuses
-- -----------------------------------------------------------------------------

INSERT INTO core.entity_types (type) VALUES
    ('ft'),
    ('bt'),
    ('epic'),
    ('story'),
    ('task'),
    ('project');


INSERT INTO core.statuses (id, name) VALUES
    (1, 'Новая'),
    (2, 'В работе'),
    (3, 'Тестирование'),
    (4, 'На уточнении'),
    (5, 'Готово');


-- =============================================================================
-- ## Relationships
--
-- core.epics.id   ←  core.stories.epic_id     (1:N)
--   Один эпик содержит множество сторей.
--   ON DELETE RESTRICT — нельзя удалить эпик, пока есть сторей.
--
-- core.stories.id ←  core.tasks.story_id      (1:N)
--   Одна стори содержит множество задач.
--   ON DELETE RESTRICT — нельзя удалить стори, пока есть задачи.
--
-- core.statuses.id ← core.tasks.status        (1:N)
--   Один статус может быть у множества задач.
--   ON DELETE RESTRICT — нельзя удалить статус, пока есть задачи.
--
-- core.tasks.id   ←  core.tasks.blocked_by    (0:1 self-ref)
--   Задача может быть заблокирована другой задачей (необязательно).
--   ON DELETE SET NULL — при удалении задачи-блокировщика поле обнуляется.
--
-- Иерархия:
--   Epic
--    └── Story
--         └── Task  (status, readiness)
--
-- Агрегация готовности:
--   Story.readiness  = AVG(tasks.readiness)   WHERE story_id = story.id
--   Epic.readiness   = AVG(stories.readiness) WHERE epic_id  = epic.id
-- =============================================================================


-- =============================================================================
-- ## Changelog
-- =============================================================================

-- 2026-04-16 | Claude | initial schema version 1.0.0: core.statuses, core.epics, core.stories, core.tasks, core.functional_requirements, core.business_requirements, core.projects, core.entity_types, core.project_entities
