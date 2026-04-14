-- =============================================================================
-- req-control · Database Schema
-- Engine: PostgreSQL 16   Schema: core
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
            ON UPDATE CASCADE
);

CREATE INDEX idx_core_tasks_story_id ON core.tasks (story_id);
CREATE INDEX idx_core_tasks_status   ON core.tasks (status);


-- -----------------------------------------------------------------------------
-- SEED core.statuses
-- -----------------------------------------------------------------------------

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
-- Иерархия:
--   Epic
--    └── Story
--         └── Task  (status, readiness)
--
-- Агрегация готовности:
--   Story.readiness  = AVG(tasks.readiness)   WHERE story_id = story.id
--   Epic.readiness   = AVG(stories.readiness) WHERE epic_id  = epic.id
-- =============================================================================
