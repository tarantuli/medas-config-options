<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManager;

require_once __DIR__ . '/bootstrap.php';

$config = sm()->resolve(ConfigManager::class);
$config->readEnv(__DIR__ . '/tests/Mockups/', '.env.test');
$config->addDirectory(__DIR__ . '/tests/MockUps');
