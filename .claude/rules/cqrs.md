## CQRS

### Принцип

Операции чтения и записи разделены на уровне интерфейсов репозиториев. Один агрегат — два контракта:

| Интерфейс | Слой | Назначение |
|-----------|------|------------|
| `TaskWriteRepositoryInterface` | Domain | Работает с доменной моделью: `create`, `update`, `delete` |
| `TaskReadRepositoryInterface` | Application | Возвращает read-DTO напрямую из SQL |

### Правила

- **`*WriteRepositoryInterface`** объявляется в **Domain**. Методы принимают и возвращают доменные объекты (`Task`, `TaskId`). Только мутации и генерация идентификаторов. **Методы чтения (`findById` и др.) запрещены** — если команде нужно загрузить агрегат, она использует `*ReadRepositoryInterface`.
- **`*ReadRepositoryInterface`** объявляется в **Application** — это контракт чтения, реализация которого обращается к инфраструктуре. Методы возвращают read-DTO (`*View`, `*Detail`, `*Summary`). Никаких мутаций.
- **UseCase-запросы** зависят только на `*ReadRepositoryInterface`.
- Реализации обоих интерфейсов — **отдельные классы** в Infrastructure-слоя.

### Структура каталогов

```
src/{Module}/
├── Domain/
│   └── Repository/
│       └── TaskWriteRepositoryInterface.php   # write — доменная модель
├── Application/
│   ├── Repository/
│   │   └── TaskReadRepositoryInterface.php    # read — контракт DTO
│   └── UseCase/
│       ├── GetTask/          # Query — зависит на TaskReadRepositoryInterface
│       └── UpdateTask/       # Command — зависит на TaskWriteRepositoryInterface
└── Infrastructure/
    └── Persistence/
        ├── PdoTaskWriteRepository.php         # реализует TaskWriteRepositoryInterface
        └── PdoTaskReadRepository.php          # реализует TaskReadRepositoryInterface
```

### Пример

```php
// Domain — write
interface TaskWriteRepositoryInterface
{
    public function create(Task $task): void;
    public function update(Task $task): void;
    public function delete(TaskId $id): void;
}

// Application — read
interface TaskReadRepositoryInterface
{
    public function findById(int $id): TaskDetailView;
    /** @return list<TaskSummaryView> */
    public function listByStoryId(int $storyId): array;
}

// Application — Command UseCase
final class UpdateTaskUseCase implements UpdateTaskUseCaseInterface
{
    public function __construct(
        private readonly TaskWriteRepositoryInterface $tasks,
    ) {}
}

// Application — Query UseCase
final class GetTaskUseCase implements GetTaskUseCaseInterface
{
    public function __construct(
        private readonly TaskReadRepositoryInterface $tasks,
    ) {}
}

// Infrastructure — write
final readonly class PdoTaskWriteRepository implements TaskWriteRepositoryInterface
{
    public function create(Task $task): void { ... }
    public function update(Task $task): void { ... }
    public function delete(TaskId $id): void { ... }
}

// Infrastructure — read
final readonly class PdoTaskReadRepository implements TaskReadRepositoryInterface
{
    public function findById(int $id): TaskDetailView { ... }
    public function listByStoryId(int $storyId): array { ... }
}
```
