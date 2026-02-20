# medas-config-options

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

---

## Description

`medas-config-options` is a typed configuration layer for the Medas framework. It gives you a structured way to declare, validate, and consume application configuration values through the service container.

Instead of reading raw strings from a config manager directly, you define your config structure as small PHP classes — one class per group, one per option — and the package handles discovery, validation, serialization, and injection automatically.

Key capabilities:

- **Declarative config structure** — define groups and options as services; the framework discovers them automatically via `ImplementorFinder`
- **Automatic injection** — inject config values into any service constructor or property using the `#[ConfigValue]` attribute, with no manual wiring
- **Validation** — options can implement `Validator` to reject bad values at resolution time
- **Serialization** — options can implement `Serializer` to transform raw config strings into typed objects
- **Scalar coercion** — string values from `.env` or YAML files are automatically cast to `bool`, `int`, `float`, or `null` based on the target parameter's declared type
- **Console command** — `config-options list` (alias: `options`) prints all registered options, their descriptions, defaults, and live values

---

## Installation

```bash
composer require morphp/medas-config-options
```

Register the package in your application bootstrap:

```php
ConfigOptionsPackage::getInstance()->initialize($config);
```

---

## Usage

### 1. Define a config group

A config group represents a namespace in your config file (e.g. `app`, `database`). Implement `ConfigGroup` and register it as a service:

```php
use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\ConfigGroup;

#[Service]
readonly class AppGroup implements ConfigGroup
{
    public function parent(): ConfigGroup|null
    {
        return null; // top-level group
    }

    public function name(): string
    {
        return 'app';
    }
}
```

Groups can be nested by returning another `ConfigGroup` from `parent()`. The full config path is built by walking up the parent chain, so a nested group `cache` under `app` produces the prefix `app.cache`.

---

### 2. Define a config option

An option maps to a single key within a group. Implement `ConfigOption` and register it as a service:

```php
use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};

#[Service]
readonly class AppNameOption implements ConfigOption
{
    public function __construct(
        private readonly AppGroup $group,
    ) {}

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'name';
    }

    public function description(): string
    {
        return 'The application name';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): mixed
    {
        return 'My App';
    }
}
```

The resolved config path for this option would be `app.name`.

---

### 3. Add validation (optional)

Implement the `Validator` interface on your option class to reject values that don't meet your requirements. An exception is thrown at resolution time if the value fails:

```php
use Medas\Core\Interfaces\Validator;

#[Service]
readonly class AppNameOption implements ConfigOption, Validator
{
    // ... group(), name(), description(), hasDefault(), default() as above

    public function isValid(mixed $value): bool
    {
        return is_string($value) && strlen($value) > 0;
    }
}
```

---

### 4. Add serialization (optional)

Implement the `Serializer` interface to transform the raw config string into a richer type before it is cached and returned:

```php
use Medas\Core\Interfaces\Serializer;

#[Service]
readonly class AllowedHostsOption implements ConfigOption, Serializer
{
    // ... group(), name(), description(), hasDefault(), default()

    public function unserialize(mixed $value): array
    {
        return array_map('trim', explode(',', $value));
    }
}
```

---

### 5. Inject a config value into a service

Use the `#[ConfigValue]` attribute on a constructor parameter, passing the fully-qualified class name of your option:

```php
use Medas\Core\Attributes\{ConfigValue, Service};

#[Service]
readonly class MyService
{
    public function __construct(
        #[ConfigValue(AppNameOption::class)] private string $appName,
    ) {}
}
```

The framework resolves the option, applies any validation and serialization, casts scalar types automatically, and injects the result. If the option has no value and no default, the parameter is left unresolved (allowing it to be `null` if the type permits).

---

### 6. Resolve a config value manually

Use the `OptionController` service directly, or the global `option()` helper:

```php
// Via service container
$name = service(OptionController::class)->getValue(service(AppNameOption::class));

// Via global helper (available after ConfigOptionsPackage is initialized)
$name = option(service(AppNameOption::class));
```

---

### 7. List all options in the console

```bash
php medas config-options:list
# or
php medas config-options:options
```

Output example:

```
app:
  # The application name, default: My App
  name: My App
  # Allowed hosts
  allowed-hosts: localhost, example.com
```

---

## Config file format

Options are read by the underlying `ConfigManager`. The resolved path for an option is constructed by joining all group names from the root down, separated by `.`, followed by the option's own name:

```
<root-group>.<child-group>.<option-name>
```

Example YAML:

```yaml
app:
  name: "My App"
  allowed-hosts: "localhost, example.com"
  cache:
    ttl: 3600
```

Example `.env`:

```
APP_NAME="My App"
APP_ALLOWED_HOSTS="localhost, example.com"
APP_CACHE_TTL=3600
```

The exact format depends on the `ConfigManager` implementation in use; `medas-config-options` is agnostic to it.
