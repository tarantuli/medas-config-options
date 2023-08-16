<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\Core\AsSingleton;
use Medas\ServiceManager\{BasePackage, ServiceConfig};

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

    public function initialize(ServiceConfig $config): void
    {
        $config->addParameterResolver(new ConfigOptionResolver());
        require_once __DIR__ . '/GlobalFunctions.php';

        parent::initialize($config);
    }
}
