<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\ServiceManager\Interfaces\ConfigManager;
use Medas\ServiceManager\ServiceManager;

chdir(__DIR__);

$sm = ServiceManager::get();

$sm->addPackage(ConfigOptionsPackage::instance());
$sm->addPackage(ConfigManagerPackage::instance());

sm()->resolve(ConfigManager::class)
    ->readEnv(__DIR__ . '/tests/Mockups/', '.env.test')
    ->addDirectory(__DIR__ . '/tests/MockUps');
