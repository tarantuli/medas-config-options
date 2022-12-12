<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\ConfigOptions\Exceptions\ConfigValueDoesNotImplementOptionException;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\{ConfigOption, ConfigValue};
use Medas\ServiceManager\ParameterResolving\ParameterResolver;

#[Service]
class ConfigOptionResolver implements ParameterResolver
{
    private mixed $result;

    public function handle(\ReflectionParameter|\ReflectionProperty $parameter): bool
    {
        if (!$attributes = $parameter->getAttributes(ConfigValue::class)) {
            return false;
        }

        $option = $this->getConfigOption($attributes[0]);
        $optionController = service(OptionController::class);

        if (!$optionController->hasValue($option)) {
            return false;
        }

        $this->result = $optionController->getValue($option);

        return true;
    }

    private function getConfigOption(\ReflectionAttribute $attribute): ConfigOption
    {
        $configOptionClass = $attribute->newInstance()->configOption;

        if (!class_exists($configOptionClass)) {
            throw new ConfigValueDoesNotImplementOptionException($configOptionClass);
        }

        /** @var ConfigOption $configOption */
        $configOption = $configOptionClass::instance();

        if (!$configOption instanceof ConfigOption) {
            throw new ConfigValueDoesNotImplementOptionException($configOptionClass);
        }

        return $configOption;
    }

    public function result(): mixed
    {
        return $this->result;
    }

    public function priority(): int
    {
        return -100;
    }
}
