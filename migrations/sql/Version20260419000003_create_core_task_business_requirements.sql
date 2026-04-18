--liquibase formatted sql

--changeset auto:create-core-task-business-requirements labels:create comment:Создание таблицы связи задач с бизнес-требованиями
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='core' AND table_name='task_business_requirements'

CREATE TABLE core.task_business_requirements (
    id                        BIGSERIAL       PRIMARY KEY,
    task_id                   BIGINT          NOT NULL,
    business_requirement_id   BIGINT          NOT NULL,
    created_at                TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at                TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_task_business_requirements
        UNIQUE (task_id, business_requirement_id),

    CONSTRAINT fk_task_bt_task_id
        FOREIGN KEY (task_id)
            REFERENCES core.tasks(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

    CONSTRAINT fk_task_bt_requirement_id
        FOREIGN KEY (business_requirement_id)
            REFERENCES core.business_requirements(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE INDEX idx_core_task_bt_task_id ON core.task_business_requirements (task_id);
CREATE INDEX idx_core_task_bt_requirement_id ON core.task_business_requirements (business_requirement_id);
--rollback DROP TABLE core.task_business_requirements;
