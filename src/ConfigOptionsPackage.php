<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\ServiceManager\{AsSingleton, BasePackage};

class ConfigOptionsPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return $this->dependenciesByClass([
        ]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }

    public function initialize(): void
    {
        sm()->config()->addParameterResolver(new ConfigOptionResolver());
        require_once __DIR__ . '/GlobalFunctions.php';

        parent::initialize();
    }
}
