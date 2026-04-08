## PHP-стиль

### Запрещено

- **Статические методы** — никаких `static function`, `::call()`, фасадов и helper-классов со статикой.
- **Геттеры и сеттеры** — никаких `getX()` / `setX()`, никаких `$object->setName('foo')`.
- **Самопорождение** — объект не создаёт сам себя: никаких `new self(...)` или `new static(...)` внутри методов экземпляра. Создание объектов — исключительно через выделенные фабрики.

### Обязательно

- **Иммутабельные объекты** — все свойства объявляются через `readonly` (PHP 8.1+) или задаются один раз в конструкторе и больше не меняются.
- Доступ к данным — через публичные `readonly`-свойства напрямую, либо через именованные методы-запросы (не `get`-префикс), которые вычисляют, а не просто возвращают поле.
- **Создание объектов** — только через фабрики (отдельные классы или функции); объект сам себя не порождает.

### Пример

```php
// Правильно
final class Money
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency,
    ) {}
}

final class MoneyFactory
{
    public function add(Money $a, Money $b): Money
    {
        return new Money($a->amount + $b->amount, $a->currency);
    }
}

// Неправильно
class Money
{
    private int $amount;
    public function getAmount(): int { return $this->amount; }
    public function setAmount(int $amount): void { $this->amount = $amount; }
    public static function create(int $amount): self { ... }
    public function add(self $other): self { return new self(...); } // самопорождение
}
```