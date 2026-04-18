--liquibase formatted sql

--changeset auto:create-core-task-functional-requirements labels:create comment:Создание таблицы связи задач с функциональными требованиями
--preconditions onFail:MARK_RAN
--precondition-sql-check expectedResult:0 SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='core' AND table_name='task_functional_requirements'

CREATE TABLE core.task_functional_requirements (
    id                          BIGSERIAL       PRIMARY KEY,
    task_id                     BIGINT          NOT NULL,
    functional_requirement_id   BIGINT          NOT NULL,
    created_at                  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at                  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_task_functional_requirements
        UNIQUE (task_id, functional_requirement_id),

    CONSTRAINT fk_task_ft_task_id
        FOREIGN KEY (task_id)
            REFERENCES core.tasks(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

    CONSTRAINT fk_task_ft_requirement_id
        FOREIGN KEY (functional_requirement_id)
            REFERENCES core.functional_requirements(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE INDEX idx_core_task_ft_task_id ON core.task_functional_requirements (task_id);
CREATE INDEX idx_core_task_ft_requirement_id ON core.task_functional_requirements (functional_requirement_id);
--rollback DROP TABLE core.task_functional_requirements;
