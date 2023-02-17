<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\ConfigOptionsTest\MockUps\MockPackage;
use Medas\ServiceManager\{Interfaces\ConfigManager, ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();
    $config->addPackages([
        ConfigOptionsPackage::instance(),
        ConfigManagerPackage::instance(),
        MockPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__ . '/tests/Mockups/', '.env.test')
    ->addDirectory(__DIR__ . '/tests/MockUps');
