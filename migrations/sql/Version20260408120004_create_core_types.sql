--liquibase formatted sql

--changeset auto:create-core-types labels:create comment:Создание таблицы types в схеме core
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='core' AND table_name='types'

CREATE TABLE core.types (
    id          BIGSERIAL       PRIMARY KEY,
    name        TEXT            NOT NULL,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);