<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\ConfigOptions\Exceptions\ConfigValueDoesNotImplementOption;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\{ConfigOption, ConfigValue};
use Medas\ServiceManager\ParameterResolving\ParameterResolver;

#[Service]
class ConfigOptionResolver implements ParameterResolver
{
    private mixed $result;

    public function priority(): int
    {
        return -100;
    }

    public function handle(\ReflectionParameter|\ReflectionProperty $parameter): bool
    {
        if (!$attributes = $parameter->getAttributes(ConfigValue::class)) {
            return false;
        }

        $option = $this->getConfigOption($attributes[0]);
        $optionController = service(OptionController::class);

        if (!$optionController->hasValue($option)) {
            // We don't throw an exception, because the parameter
            // could be nullable, which means an unset config value
            // is allowed
            return false;
        }

        $this->result = $optionController->getValue($option);

        return true;
    }

    private function getConfigOption(\ReflectionAttribute $attribute): ConfigOption
    {
        $configOptionClass = $attribute->newInstance()->configOption;

        if (!class_exists($configOptionClass)) {
            throw new ConfigValueDoesNotImplementOption($configOptionClass);
        }

        /** @var ConfigOption $configOption */
        $configOption = $configOptionClass::instance();

        if (!$configOption instanceof ConfigOption) {
            throw new ConfigValueDoesNotImplementOption($configOptionClass);
        }

        return $configOption;
    }

    public function result(): mixed
    {
        return $this->result;
    }
}
