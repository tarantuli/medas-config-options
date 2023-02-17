<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\Functional;

use Medas\ConfigOptions\Exceptions\ConfigValueDoesNotImplementOption;
use Medas\ConfigOptionsTest\MockUps\{MockServiceWithConfigInjection, MockServiceWithInvalidConfigInjection};
use Medas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class InjectionsTest extends TestCase
{
    public function testServiceWithConfigInjection(): void
    {
        $manager = ServiceManager::get();

        $service = $manager->resolve(MockServiceWithConfigInjection::class);

        $this->assertEquals('service-manager', $service->getProject());
    }

    public function testServiceWithInvalidConfigInjection(): void
    {
        $manager = ServiceManager::get();

        $this->expectException(ConfigValueDoesNotImplementOption::class);
        $manager->resolve(MockServiceWithInvalidConfigInjection::class);
    }
}
