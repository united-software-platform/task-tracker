## Symfony-правила

### Конфигурация

- **Только PHP** — все конфигурации пишутся исключительно в `.php`-файлах (`config/*.php`, `config/packages/*.php`, `config/routes/*.php`).
- **Запрещены** любые конфигурационные файлы в форматах YAML (`.yaml`, `.yml`) и XML (`.xml`) — включая `services.yaml`, `routes.yaml`, `packages/*.yaml` и любые другие.
- **Запрещено** создавать новые YAML/XML конфиги, даже если Symfony поддерживает такой формат.

#### Пример

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->secret('%env(APP_SECRET)%');
    $framework->httpMethodOverride(false);
};
```

```php
// config/services.php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->load('App\\', '../src/')
        ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');
};
```

```php
// config/routes.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('../src/Controller/', 'attribute');
};
```