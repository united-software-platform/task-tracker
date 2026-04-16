## Чистая архитектура (Robert C. Martin)

### Слои и направление зависимостей

Зависимости направлены **строго внутрь**: `Infrastructure → Application → Domain`.
Внутренние слои ничего не знают о внешних.

```
┌─────────────────────────────────┐
│         Infrastructure          │  Frameworks, DB, HTTP, CLI, Queue
│  ┌───────────────────────────┐  │
│  │       Application         │  │  Use Cases, Commands, Queries, DTO
│  │  ┌─────────────────────┐  │  │
│  │  │       Domain        │  │  │  Entities, Value Objects, Events,
│  │  │                     │  │  │  Domain Services, Repository interfaces
│  │  └─────────────────────┘  │  │
│  └───────────────────────────┘  │
└─────────────────────────────────┘
```

---

### Domain (ядро, без зависимостей на фреймворк)

- **Entities** — бизнес-объекты с идентичностью; иммутабельные свойства через `readonly`.
- **Value Objects** — объекты без идентичности, определяются значением; всегда иммутабельны.
- **Domain Events** — факты, случившиеся в домене; только данные, без логики вызова.
- **Repository interfaces** — контракты (`interface`) для доступа к данным; реализация — в Infrastructure.
- **Domain Services** — операции над несколькими агрегатами, не принадлежащие одному Entity.

**Запрещено в Domain:**
- Любые зависимости на Symfony, Doctrine, PSR-контейнер, HTTP и т.п.
- Статические методы, геттеры/сеттеры, самопорождение (`new self`).

---

### Application (оркестрация, use cases)

Каждая бизнес-операция — отдельный UseCase. Один UseCase — одно действие (SRP).

#### Структура UseCase

| Класс | Суффикс | Назначение |
|-------|---------|------------|
| `ApproveRequirementUseCase` | `UseCase` | Реализация; `final class` |
| `ApproveRequirementUseCaseInterface` | `UseCaseInterface` | Контракт; зависимость контроллера — на интерфейс |
| `ApproveRequirementInput` | `Input` | Иммутабельный входной DTO (`readonly`) |
| `ApproveRequirementOutput` | `Output` | Иммутабельный выходной DTO (`readonly`); если нет данных — `void` |

#### Правила

- Единственный публичный метод: `execute(XxxInput $input): XxxOutput|void`.
- `Input` и `Output` — `readonly`-объекты без логики.
- UseCase зависит только на **Domain**-интерфейсы, не на конкретные реализации.
- Каждый UseCase живёт в собственном каталоге: `Application/UseCase/ApproveRequirement/`.

**Запрещено в Application:**
- Прямые вызовы Doctrine, PDO, HTTP-клиентов — только через Domain-интерфейсы.
- Бизнес-логика (инварианты, расчёты) — она принадлежит Domain.

---

### Infrastructure (детали реализации)

- **Repository implementations** — реализуют Domain-интерфейсы через Doctrine/PDO/etc.
- **Controllers** — принимают HTTP-запрос, вызывают Application-хендлер, возвращают ответ.
- **Persistence (Entities mapping)** — Doctrine mapping-конфигурации.
- **External services** — HTTP-клиенты, очереди, файловая система.

**Запрещено в Infrastructure:**
- Бизнес-логика.
- Прямые зависимости контроллера на Domain-объекты, минуя Application.

---

### SOLID

| Принцип | Правило |
|---------|---------|
| **SRP** — Single Responsibility | Один класс — одна причина меняться. Handler обрабатывает ровно одну команду. |
| **OCP** — Open/Closed | Расширять поведение через новые классы (новый Handler, новый Specification), не модифицируя существующие. |
| **LSP** — Liskov Substitution | Реализация интерфейса полностью соблюдает его контракт; не бросает неожиданных исключений, не игнорирует параметры. |
| **ISP** — Interface Segregation | Интерфейсы узкие и сфокусированные. `UserRepository` не содержит методов `OrderRepository`. |
| **DIP** — Dependency Inversion | Верхние слои зависят на абстракции (интерфейсы), объявленные в Domain. Infrastructure подставляет конкретные реализации через DI-контейнер. |

---

### Структура каталогов

Код организован по **доменным модулям**. Каждый модуль — самостоятельный bounded context со своими слоями.

```
src/
└── {DomainModule}/               # например: Requirement, User, Project
    ├── Domain/
    │   ├── Model/          # Entities, Value Objects
    │   ├── Event/          # Domain Events
    │   ├── Repository/     # Interfaces
    │   └── Service/        # Domain Services
    ├── Application/
    │   └── UseCase/
    │       ├── ApproveRequirement/   # Input, Output, Interface, UseCase
    │       └── GetRequirement/       # Input, Output, Interface, UseCase
    └── Infrastructure/
        ├── Persistence/    # Doctrine Repositories, Migrations
        ├── Http/           # Controllers
        └── External/       # HTTP-клиенты, очереди
```

---

### Пример

```php
// Domain — интерфейс репозитория
namespace App\Requirement\Domain\Repository;

interface RequirementRepositoryInterface
{
    public function findById(RequirementId $id): Requirement;
    public function save(Requirement $requirement): void;
}

// Application — UseCase
namespace App\Requirement\Application\UseCase\ApproveRequirement;

final class ApproveRequirementInput
{
    public function __construct(
        public readonly string $requirementId,
        public readonly string $approvedBy,
    ) {}
}

interface ApproveRequirementUseCaseInterface
{
    public function execute(ApproveRequirementInput $input): void;
}

final class ApproveRequirementUseCase implements ApproveRequirementUseCaseInterface
{
    public function __construct(
        private readonly RequirementRepositoryInterface $requirements,
    ) {}

    public function execute(ApproveRequirementInput $input): void
    {
        $requirement = $this->requirements->findById(
            new RequirementId($input->requirementId)
        );
        $requirement->approve($input->approvedBy);
        $this->requirements->save($requirement);
    }
}

// Infrastructure — реализация репозитория
namespace App\Requirement\Infrastructure\Persistence;

final class DoctrineRequirementRepository implements RequirementRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function findById(RequirementId $id): Requirement
    {
        return $this->em->find(Requirement::class, $id->value)
            ?? throw new RequirementNotFound($id);
    }

    public function save(Requirement $requirement): void
    {
        $this->em->persist($requirement);
    }
}
```