--liquibase formatted sql

--changeset auto:add-core-epics-assignee labels:add comment:Добавление колонки assignee в таблицу core.epics
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='epics' AND column_name='assignee'

ALTER TABLE core.epics
    ADD COLUMN assignee VARCHAR(200) NULL;

--changeset auto:add-core-epics-approver labels:add comment:Добавление колонки approver в таблицу core.epics
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='epics' AND column_name='approver'

ALTER TABLE core.epics
    ADD COLUMN approver VARCHAR(200) NULL;

--changeset auto:add-core-stories-assignee labels:add comment:Добавление колонки assignee в таблицу core.stories
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='stories' AND column_name='assignee'

ALTER TABLE core.stories
    ADD COLUMN assignee VARCHAR(200) NULL;

--changeset auto:add-core-stories-approver labels:add comment:Добавление колонки approver в таблицу core.stories
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='stories' AND column_name='approver'

ALTER TABLE core.stories
    ADD COLUMN approver VARCHAR(200) NULL;

--changeset auto:add-core-tasks-approver labels:add comment:Добавление колонки approver в таблицу core.tasks
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='core' AND table_name='tasks' AND column_name='approver'

ALTER TABLE core.tasks
    ADD COLUMN approver VARCHAR(200) NULL;
