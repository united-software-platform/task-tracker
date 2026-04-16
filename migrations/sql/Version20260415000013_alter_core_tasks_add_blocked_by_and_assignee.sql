--liquibase formatted sql

--changeset gaybovich:Version20260415000013_alter_core_tasks_add_blocked_by labels:add comment:Добавление поля blocked_by в таблицу core.tasks
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='tasks' AND column_name='blocked_by'

ALTER TABLE core.tasks
    ADD COLUMN blocked_by BIGINT NULL,
    ADD CONSTRAINT fk_tasks_blocked_by
        FOREIGN KEY (blocked_by)
            REFERENCES core.tasks(id)
            ON DELETE SET NULL;

CREATE INDEX idx_core_tasks_blocked_by ON core.tasks (blocked_by);

--changeset gaybovich:Version20260415000013_alter_core_tasks_add_assignee labels:add comment:Добавление поля assignee в таблицу core.tasks
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='tasks' AND column_name='assignee'

ALTER TABLE core.tasks
    ADD COLUMN assignee VARCHAR(200) NULL;                        -- имя/логин исполнителя; NULL = нет исполнителя
