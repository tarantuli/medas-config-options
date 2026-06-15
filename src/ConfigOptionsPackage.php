<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\Core\{AsSingleton, BasePackage, Interfaces\ServiceConfigBuilder};

class ConfigOptionsPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }

    public function initialize(ServiceConfigBuilder $config): void
    {
        $config->addParameterResolver(ConfigOptionResolver::class);

        require_once __DIR__ . '/GlobalFunctions.php';

        parent::initialize($config);
    }
}
