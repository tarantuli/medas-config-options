<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    HandlesByCompiling,
    Interfaces\CompilableParameterResolver,
    Interfaces\ConfigOption,
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

        return new CompiledConfigValue($configValue->configOption, $types);
    }

    /**
     * @param CompiledConfigValue $compiled
     */
    public function resolveCompiled(object $compiled): ParameterResolverResult
    {
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
