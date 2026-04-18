--liquibase formatted sql

--changeset auto:add-core-tasks-plan labels:add comment:Добавление колонки plan в таблицу core.tasks
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='tasks' AND column_name='plan'

ALTER TABLE core.tasks
    ADD COLUMN plan TEXT NULL;

--changeset auto:add-core-tasks-model labels:add comment:Добавление колонки model в таблицу core.tasks
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='tasks' AND column_name='model'

ALTER TABLE core.tasks
    ADD COLUMN model TEXT NULL;
