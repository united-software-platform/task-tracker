какdh-- =============================================================================
-- req-control · Database Schema
-- Engine: PostgreSQL 16   Schema: core
-- =============================================================================
--
-- Модель данных состоит из двух таблиц: справочник типов рабочих элементов
-- (types) и сами элементы (tasks). Иерархия задач реализована через паттерн
-- Nested Sets, что позволяет эффективно получать поддеревья и предков
-- одним запросом без рекурсии.
-- =============================================================================


-- -----------------------------------------------------------------------------
-- Schema
-- -----------------------------------------------------------------------------

CREATE SCHEMA IF NOT EXISTS core;


-- -----------------------------------------------------------------------------
-- TABLE core.types
-- Справочник типов рабочих элементов (Задача / Стори / Эпик).
-- -----------------------------------------------------------------------------

CREATE TABLE core.types (
    id          BIGSERIAL       PRIMARY KEY,
    name        TEXT            NOT NULL,                    -- человекочитаемое название типа
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);


-- -----------------------------------------------------------------------------
-- TABLE core.tasks
-- Рабочие элементы. Иерархия хранится через Nested Sets (lft / rgt / depth).
-- Одновременно поддерживается parent_id для быстрого обхода прямых потомков.
-- -----------------------------------------------------------------------------

CREATE TABLE core.tasks (
    id          BIGSERIAL       PRIMARY KEY,
    title       VARCHAR(200)    NOT NULL,
    status      SMALLINT        NOT NULL,                    -- числовой код статуса; интерпретируется на уровне приложения
    type        SMALLINT        NOT NULL,                    -- ссылка на core.types.id
    parent_id   BIGINT,                                      -- NULL = корневой узел (Эпик)
    lft         INTEGER         NOT NULL DEFAULT 0,          -- левая граница поддерева (Nested Sets)
    rgt         INTEGER         NOT NULL DEFAULT 0,          -- правая граница поддерева (Nested Sets)
    depth       INTEGER         NOT NULL DEFAULT 0           -- глубина: 0 = root, 1 = Стори, 2 = Задача
                                         CHECK (depth >= 0),
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_tasks_type
        FOREIGN KEY (type)
            REFERENCES core.types(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,

    CONSTRAINT fk_tasks_parent_id
        FOREIGN KEY (parent_id)
            REFERENCES core.tasks(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE INDEX idx_core_tasks_parent_id ON core.tasks (parent_id);
CREATE INDEX idx_core_tasks_lft_rgt   ON core.tasks (lft, rgt);


-- -----------------------------------------------------------------------------
-- SEED core.types
-- -----------------------------------------------------------------------------

INSERT INTO core.types (id, name) VALUES
    (1, 'Задача'),
    (2, 'Стори'),
    (3, 'Эпик');

SELECT setval(pg_get_serial_sequence('core.types', 'id'), 3);


-- =============================================================================
-- ## Relationships
--
-- core.types.id  ←  core.tasks.type        (one-to-many)
--   Один тип может быть присвоен множеству задач.
--   ON DELETE RESTRICT — нельзя удалить тип, пока есть ссылающиеся задачи.
--   ON UPDATE CASCADE  — при смене id типа ссылки обновляются автоматически.
--
-- core.tasks.id  ←  core.tasks.parent_id   (one-to-many, self-reference)
--   Задача может иметь одного родителя и множество потомков.
--   NULL в parent_id обозначает корневой узел (Эпик).
--   ON DELETE RESTRICT — нельзя удалить узел, у которого есть дочерние узлы.
--   ON UPDATE CASCADE  — при смене id родителя ссылки обновляются автоматически.
--
-- Ожидаемая иерархия глубины:
--   Эпик   (depth=0, type=3, parent_id IS NULL)
--    └── Стори  (depth=1, type=2, parent_id = эпик.id)
--         └── Задача (depth=2, type=1, parent_id = стори.id)
--
-- Nested Sets — типовые запросы:
--   Всё поддерево  : WHERE lft BETWEEN :lft AND :rgt
--   Все предки     : WHERE lft < :lft AND rgt > :rgt
--   Прямые дети    : WHERE parent_id = :id          -- idx_core_tasks_parent_id
--   Узел — лист    : rgt - lft = 1
-- =============================================================================
