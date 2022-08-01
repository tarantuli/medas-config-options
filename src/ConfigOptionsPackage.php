<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ServiceManager\{AsSingleton, BasePackage};

class ConfigOptionsPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return $this->dependenciesByClass([
            ConfigManagerPackage::class,
        ]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }

    public function initialize(): void
    {
        sm()->addParameterResolver(new ConfigOptionResolver());

        parent::initialize();
    }
}
