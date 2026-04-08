# Схема данных

## Схема `core`

### `core.types`
Справочник типов задач.

| Колонка    | Тип          | Описание         |
|------------|--------------|------------------|
| id         | BIGSERIAL PK |                  |
| name       | TEXT         | Название типа    |
| created_at | TIMESTAMPTZ  |                  |
| updated_at | TIMESTAMPTZ  |                  |

Предустановленные значения: `Задача`, `Стори`, `Эпик`.

---

### `core.tasks`
Задачи с иерархической структурой на основе Nested Sets.

| Колонка    | Тип           | Описание                              |
|------------|---------------|---------------------------------------|
| id         | BIGSERIAL PK  |                                       |
| title      | VARCHAR(200)  | Название задачи                       |
| status     | SMALLINT      | Статус задачи                         |
| type       | SMALLINT      | Тип задачи (→ core.types)             |
| parent_id  | SMALLINT      | ID родительской задачи                |
| lft        | INTEGER       | Левая граница (Nested Sets)           |
| rgt        | INTEGER       | Правая граница (Nested Sets)          |
| depth      | INTEGER       | Глубина вложенности                   |
| created_at | TIMESTAMPTZ   |                                       |
| updated_at | TIMESTAMPTZ   |                                       |

**Индексы:**
- `idx_core_tasks_parent_id` — по `parent_id`
- `idx_core_tasks_lft_rgt` — по `(lft, rgt)`

## Иерархия задач

```
Эпик
 └── Стори
      └── Задача
```

Иерархия реализована через алгоритм Nested Sets: поля `lft`, `rgt`, `depth` позволяют
получить всё поддерево задачи одним запросом без рекурсии.
