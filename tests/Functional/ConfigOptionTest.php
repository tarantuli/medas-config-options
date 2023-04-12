<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\Functional;

use Medas\ConfigOptions\OptionController;
use Medas\ConfigOptionsTest\MockUps\MockConfigOption;
use Medas\Core\Interfaces\ConfigManager;
use PHPUnit\Framework\TestCase;

class ConfigOptionTest extends TestCase
{
    public function testOptionPath(): void
    {
        $option = MockConfigOption::instance();
        $controller = service(OptionController::class);
        $manager = service(ConfigManager::class);

        $value = $manager->getValue($controller->getPath($option));

        self::assertEquals('service-manager', $value);
    }

    public function testOptionValidator(): void
    {
        $option = MockConfigOption::instance();

        self::assertNotTrue($option->isValid(false));
        self::assertTrue($option->isValid('string'));
    }
}
