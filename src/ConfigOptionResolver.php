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

        return new ParameterResolverResult(true, $optionController->getValue($option));
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
}
