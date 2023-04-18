<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\Functional;

use Medas\ConfigOptions\Exceptions\ConfigValueDoesNotImplementOption;
use Medas\ConfigOptionsTest\MockUps\{MockServiceWithConfigInjection, MockServiceWithInvalidConfigInjection};
use PHPUnit\Framework\TestCase;

class InjectionsTest extends TestCase
{
    public function testServiceWithConfigInjection(): void
    {
        $service = service(MockServiceWithConfigInjection::class);

        $this->assertEquals('service-manager', $service->getProject());
    }

    public function testServiceWithInvalidConfigInjection(): void
    {
        $this->expectException(ConfigValueDoesNotImplementOption::class);
        service(MockServiceWithInvalidConfigInjection::class);
    }
}
