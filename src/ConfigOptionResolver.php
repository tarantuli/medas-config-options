<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Interfaces\ConfigOption,
    Interfaces\ParameterResolver,
    ParameterResolverResult
};

#[Service]
class ConfigOptionResolver implements ParameterResolver
{
    public function priority(): int
    {
        return -100;
    }

    public function handle(\ReflectionParameter|\ReflectionProperty $parameter): ParameterResolverResult
    {
        if (!$attributes = $parameter->getAttributes(ConfigValue::class)) {
            return new ParameterResolverResult(false);
        }

        $option = $this->getConfigOption($attributes[0]);
        $optionController = service(OptionController::class);

        if (!$optionController->hasValue($option)) {
            // We don't throw an exception because the parameter
            // could be nullable, which means an unset config value
            // is allowed
            return new ParameterResolverResult(false);
        }

        $value = $optionController->getValue($option);

        $this->deserializeValue($parameter, $value);

        return new ParameterResolverResult(true, $value);
    }

    private function getConfigOption(\ReflectionAttribute $attribute): ConfigOption
    {
        /** @var ConfigValue $configValue */
        $configValue = $attribute->newInstance();
        $configOptionClass = $configValue->configOption;

        if (!class_exists($configOptionClass)) {
            throw new Exceptions\ConfigValueDoesNotImplementOption($configOptionClass);
        }

        $configOption = service($configOptionClass);

        if (!$configOption instanceof ConfigOption) {
            throw new Exceptions\ConfigValueDoesNotImplementOption($configOptionClass);
        }

        return $configOption;
    }

    private function deserializeValue(\ReflectionParameter|\ReflectionProperty $parameter, mixed &$value): void
    {
        $types = parameterTypes($parameter);

        foreach ($types as $type) {
            if ($type->getName() === 'bool') {
                if ($value === 'true') {
                    $value = true;
                }
                elseif ($value === 'false') {
                    $value = false;
                }
            }

            if ($type->getName() === 'int' && is_string($value) && ctype_digit(ltrim($value, '-'))) {
                $value = (int) $value;
            }

            if ($type->getName() === 'float' && is_string($value) && is_numeric($value)) {
                $value = (float) $value;
            }

            if ($type->allowsNull() && $value === 'null') {
                $value = null;
            }
        }
    }
}
