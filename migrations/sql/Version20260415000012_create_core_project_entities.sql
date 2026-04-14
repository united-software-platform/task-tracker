--liquibase formatted sql

--changeset gaybovich:create-core-project_entities labels:create comment:Создание таблицы связи project_entities в схеме core
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='core' AND table_name='project_entities'

CREATE TABLE core.project_entities (
    id             BIGSERIAL       PRIMARY KEY,
    project_id     BIGINT          NOT NULL,
    entity_type_id BIGINT          NOT NULL,
    entity_id      BIGINT          NOT NULL,
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
