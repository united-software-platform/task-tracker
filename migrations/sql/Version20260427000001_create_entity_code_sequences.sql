--liquibase formatted sql

--changeset gaybovich:create-entity-code-sequences labels:create comment:Создание последовательностей для генерации кодов сущностей

CREATE SEQUENCE IF NOT EXISTS core.entity_code_epic_seq;
CREATE SEQUENCE IF NOT EXISTS core.entity_code_stry_seq;
CREATE SEQUENCE IF NOT EXISTS core.entity_code_task_seq;
CREATE SEQUENCE IF NOT EXISTS core.entity_code_btrq_seq;
CREATE SEQUENCE IF NOT EXISTS core.entity_code_ftrq_seq;
CREATE SEQUENCE IF NOT EXISTS core.entity_code_nfrq_seq;
