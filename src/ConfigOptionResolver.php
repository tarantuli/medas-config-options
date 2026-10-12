<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    HandlesByCompiling,
    Interfaces\CompilableParameterResolver,
    Interfaces\ConfigManager,
    Interfaces\ConfigOption,
    Interfaces\Serializer,
    Interfaces\Validator,
    ParameterResolverResult
};

#[Service]
class ConfigOptionResolver implements CompilableParameterResolver
{
    use HandlesByCompiling;

    public function priority(): int
    {
        return -100;
    }

    public function compile(\ReflectionParameter|\ReflectionProperty $parameter): CompiledConfigValue|null
    {
        if (!$attributes = $parameter->getAttributes(ConfigValue::class)) {
            return null;
        }

        /** @var ConfigValue $configValue */
        $configValue = $attributes[0]->newInstance();

        $types = array_map(
            static fn(\ReflectionNamedType $type): array => [$type->getName(), $type->allowsNull()],
            normalizeType($parameter->getType())
        );

        $option = $this->getConfigOption($configValue->configOption);
        $hasDefault = $option->hasDefault();
        $default = $hasDefault ? $option->default() : null;
        $defaultThroughOption = !$this->canBeStored($default);

        return new CompiledConfigValue(
            configOption: $configValue->configOption,
            types: $types,
            configuredValueThroughOption: $option instanceof Validator || $option instanceof Serializer,
            defaultThroughOption: $defaultThroughOption,
            path: service(OptionController::class)->getPath($option),
            hasDefault: $hasDefault,
            default: $defaultThroughOption ? null : $default,
            sourceFiles: $this->sourceFiles($option),
        );
    }

    // The compiled plan is cached in serialized form, and an object other than an enum case
    // would come back as a different instance, so such a default is asked of the option itself
    private function canBeStored(mixed $value): bool
    {
        if (is_object($value)) {
            return $value instanceof \UnitEnum;
        }

        if (is_array($value)) {
            return array_all($value, fn(mixed $item): bool => $this->canBeStored($item));
        }

        return true;
    }

    /** @return string[] */
    private function sourceFiles(ConfigOption $option): array
    {
        $files = [new \ReflectionClass($option)->getFileName()];
        $group = $option->group();

        do {
            $files[] = new \ReflectionClass($group)->getFileName();
        } while ($group = $group->parent());

        return array_values(array_filter($files));
    }

    /**
     * @param CompiledConfigValue $compiled
     */
    public function resolveCompiled(object $compiled): ParameterResolverResult
    {
        $configManager = service(ConfigManager::class);
        $isConfigured = $configManager->hasValue($compiled->path);

        $needsOption = $isConfigured
            ? $compiled->configuredValueThroughOption
            : $compiled->defaultThroughOption;

        if (!$needsOption) {
            return $this->resolveByPath($compiled, $configManager, $isConfigured);
        }

        $option = $this->getConfigOption($compiled->configOption);
        $optionController = service(OptionController::class);

        if (!$optionController->hasValue($option)) {
            // We don't throw an exception because the parameter
            // could be nullable, which means an unset config value
            // is allowed
            return new ParameterResolverResult(false);
        }

        $value = $optionController->getValue($option);

        $this->deserializeValue($compiled->types, $value);

        return new ParameterResolverResult(true, $value);
    }

    // The same outcome as OptionController::hasValue() and getValue() wherever the option has
    // no say (see CompiledConfigValue), but without creating the option and its groups
    private function resolveByPath(
        CompiledConfigValue $compiled,
        ConfigManager       $configManager,
        bool                $isConfigured,
    ): ParameterResolverResult
    {
        if ($isConfigured) {
            $value = $configManager->getValue($compiled->path);
        }
        elseif ($compiled->hasDefault) {
            $value = $compiled->default;
        }
        else {
            return new ParameterResolverResult(false);
        }

        $this->deserializeValue($compiled->types, $value);

        return new ParameterResolverResult(true, $value);
    }

    private function getConfigOption(string $configOptionClass): ConfigOption
    {
        if (!class_exists($configOptionClass)) {
            throw new Exceptions\ConfigValueDoesNotImplementOption($configOptionClass);
        }

        $configOption = service($configOptionClass);

        if (!$configOption instanceof ConfigOption) {
            throw new Exceptions\ConfigValueDoesNotImplementOption($configOptionClass);
        }

        return $configOption;
    }

    /**
     * @param list<array{string, bool}> $types
     */
    private function deserializeValue(array $types, mixed &$value): void
    {
        foreach ($types as [$typeName, $allowsNull]) {
            if ($typeName === 'bool') {
                if ($value === 'true') {
                    $value = true;
                }
                elseif ($value === 'false') {
                    $value = false;
                }
            }

            if ($typeName === 'int' && is_string($value) && ctype_digit(ltrim($value, '-'))) {
                $value = (int) $value;
            }

            if ($typeName === 'float' && is_string($value) && is_numeric($value)) {
                $value = (float) $value;
            }

            if ($allowsNull && $value === 'null') {
                $value = null;
            }
        }
    }
}
