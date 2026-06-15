<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\ConfigOptionsTest\MockUps\MockPackage;
use Medas\ConsolePrinter\ConsolePrinterPackage;
use Medas\Core\Interfaces\ConfigManager;
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class);

    $config->addPackages([
        ConfigOptionsPackage::instance(),
        ConfigManagerPackage::instance(),
        ConsolePrinterPackage::instance(),
        MockPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__ . '/tests/Mockups/', '.env.test')
    ->addDirectory(__DIR__ . '/tests/MockUps');
