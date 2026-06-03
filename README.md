# medas-config-options

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

Provides a typed, declarative configuration layer on top of `medas-config-manager`. Rather than reading raw strings by path, you define small PHP classes — one per group, one per option — and the framework discovers them automatically, resolves their values from the config manager, and injects them into services via the `#[ConfigValue]` attribute.

Key features:

- **Declarative structure** — `ConfigGroup` and `ConfigOption` classes are discovered at runtime via `ImplementorFinder`; no manual registration is required
- **Automatic injection** — `#[ConfigValue(SomeOption::class)]` on a constructor parameter is resolved and injected without any wiring code; the `ConfigOptionResolver` parameter resolver handles it at priority −100
- **Validation** — options can implement `Validator`; an exception is thrown at resolution time if `isValid()` returns `false`
- **Serialization** — options can implement `Serializer`; `unserialize()` transforms the raw config value into any typed object before it is cached and returned
- **Scalar coercion** — string values from YAML or `.env` are automatically cast to `bool`, `int`, `float`, or `null` based on the declared type of the injection target
- **Result caching** — resolved values are memoised in an `SplObjectStorage` on `OptionController` for the lifetime of the request
- **`option()` global helper** — convenience function: `option(service(MyOption::class))`
- **Console command** — `config-options:list` (alias: `config-options:options`) prints all registered options grouped by hierarchy, with descriptions, defaults, and live values

## Usage

### Package developer context

Register the package with the framework bootstrapper:

```php
use Medas\ConfigOptions\ConfigOptionsPackage;

// Registers the ConfigOptionResolver parameter resolver and the option() global function
ConfigOptionsPackage::instance()->initialize($config);
```

**Defining a config group:**

```php
use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup};

#[Service]
readonly class MailGroup implements ConfigGroup
{
    public function parent(): ConfigGroup|null
    {
        // null = top-level group; the path prefix is just 'mail'
        return null;
    }

    public function name(): string
    {
        return 'mail';
    }
}
```

Groups can be nested by returning another `ConfigGroup` from `parent()`. The resolved config path is built by walking up the chain, so a group `smtp` under `mail` yields the prefix `mail.smtp`.

**Defining a config option:**

```php
use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};

#[Service]
readonly class MailFromAddress implements ConfigOption
{
    public function __construct(
        private MailGroup $group,
    ) {}

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'from-address';
    }

    public function description(): string
    {
        return 'The address used as the sender on outgoing mail';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): mixed
    {
        return 'noreply@example.com';
    }
}
```

The resolved config path for this option is `mail.from-address`.

**Adding validation:**

```php
use Medas\Core\Interfaces\Validator;

#[Service]
readonly class MailFromAddress implements ConfigOption, Validator
{
    // ... group(), name(), description(), hasDefault(), default() as above

    public function isValid(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
```

`ValueDoesNotPassValidator` is thrown at resolution time if the live value fails `isValid()`.

**Adding serialization:**

```php
use Medas\Core\Interfaces\Serializer;

#[Service]
readonly class AllowedRecipientsOption implements ConfigOption, Serializer
{
    // ... group(), name(), description(), hasDefault(), default()

    public function unserialize(mixed $value): array
    {
        // Raw config value: "alice@example.com, bob@example.com"
        return array_map('trim', explode(',', $value));
    }
}
```

**Injecting a config value into a service:**

```php
use Medas\Core\Attributes\{ConfigValue, Service};

#[Service]
readonly class Mailer
{
    public function __construct(
        #[ConfigValue(MailFromAddress::class)]
        private string $fromAddress,

        #[ConfigValue(AllowedRecipientsOption::class)]
        private array $allowedRecipients,
    ) {}
}
```

The resolver fetches the option, runs validation and serialization, applies scalar coercion if needed, then injects the result. If an option has no value and no default, and the parameter type permits `null`, it is injected as `null` rather than throwing.

**Resolving a value manually:**

```php
use Medas\ConfigOptions\OptionController;

// Via injected OptionController
$fromAddress = $this->optionController->getValue(service(MailFromAddress::class));

// Via the global helper (available after the package is initialized)
$fromAddress = option(service(MailFromAddress::class));
```

**Checking whether a value exists:**

```php
if ($this->optionController->hasValue(service(MailFromAddress::class))) {
    $fromAddress = $this->optionController->getValue(service(MailFromAddress::class));
}
```

`hasValue()` returns `true` when either the config manager has a value at the resolved path or the option has a default.

### Backend user context

**Configuring options in YAML:**

Given the group/option definitions above, the YAML key is the dot-separated path:

```yaml
mail:
  from-address: "sender@myapp.com"
  allowed-recipients: "alice@myapp.com, bob@myapp.com"
  smtp:
    host: "smtp.myapp.com"
    port: 587
```

**Configuring options via `.env`:**

The config manager resolves `$env(KEY)` tokens in YAML values, so secrets can be kept out of committed files:

```yaml
mail:
  from-address: $env(MAIL_FROM_ADDRESS)
```

```
MAIL_FROM_ADDRESS=sender@myapp.com
```

**Listing all options from the console:**

```bash
php bin/medas config-options:list
# or
php bin/medas config-options:options

# Filter by name or description substring
php bin/medas config-options:list from
```

Example output:

```
mail:
  # The address used as the sender on outgoing mail, default: noreply@example.com
  from-address: sender@myapp.com
  # Comma-separated list of allowed recipients
  allowed-recipients: "alice@myapp.com, bob@myapp.com"
```
