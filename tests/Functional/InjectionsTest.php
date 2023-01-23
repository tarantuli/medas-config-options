<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\Functional;

use Medas\ConfigOptions\Exceptions\ConfigValueDoesNotImplementOption;
use Medas\ConfigOptionsTest\MockUps\MockServiceWithConfigInjection;
use Medas\ConfigOptionsTest\MockUps\MockServiceWithInvalidConfigInjection;
use Medas\ConfigOptionsTest\MockUps\MockPackage;
use Medas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class InjectionsTest extends TestCase
{
    public function testServiceWithConfigInjection(): void
    {
        $manager = $this->loadMockUps();

        /** @var $service MockServiceWithConfigInjection */
        $service = $manager->resolve(MockServiceWithConfigInjection::class);

        $this->assertEquals('service-manager', $service->getProject());
    }

    public function testServiceWithInvalidConfigInjection(): void
    {
        $manager = $this->loadMockUps();

        $this->expectException(ConfigValueDoesNotImplementOption::class);
        $manager->resolve(MockServiceWithInvalidConfigInjection::class);
    }

    protected function loadMockUps(): ServiceManager
    {
        $manager = ServiceManager::get();
        $manager->addPackage(MockPackage::instance());

        return $manager;
    }
}
