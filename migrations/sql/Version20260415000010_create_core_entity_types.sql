--liquibase formatted sql

--changeset gaybovich:create-core-entity_types labels:create comment:Создание таблицы entity_types в схеме core
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='core' AND table_name='entity_types'

CREATE TABLE core.entity_types (
    id         BIGSERIAL       PRIMARY KEY,
    type       VARCHAR(50)     NOT NULL UNIQUE,
    created_at TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);
