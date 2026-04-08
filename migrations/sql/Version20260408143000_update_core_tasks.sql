--liquibase formatted sql

--changeset auto:update-core-tasks-fk labels:update comment:Добавление внешних ключей tasks.type -> types.id и tasks.parent_id -> tasks.id

ALTER TABLE core.tasks
    ADD CONSTRAINT fk_tasks_type
        FOREIGN KEY (type) REFERENCES core.types(id),
    ADD CONSTRAINT fk_tasks_parent_id
        FOREIGN KEY (parent_id) REFERENCES core.tasks(id);
