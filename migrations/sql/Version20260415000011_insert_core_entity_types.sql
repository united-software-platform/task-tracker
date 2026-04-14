Те--liquibase formatted sql

--changeset gaybovich:insert-core-entity_types labels:insert comment:Начальное заполнение таблицы core.entity_types

INSERT INTO core.entity_types (type) VALUES
    ('ft'),
    ('bt'),
    ('epic'),
    ('story'),
    ('task'),
    ('project');
