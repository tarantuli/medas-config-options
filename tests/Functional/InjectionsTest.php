<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\Functional;

use Medas\ConfigOptions\{
    CompiledConfigValue,
    ConfigOptionResolver,
    Exceptions\ConfigValueDoesNotImplementOption
};
use Medas\ConfigOptionsTest\MockUps\{
    MockConfigGroup,
    MockOptionWithDefault,
    MockServiceWithConfigInjection,
    MockServiceWithDefaultInjection,
    MockServiceWithInvalidConfigInjection,
    MockServiceWithUppercasedInjection
};
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

    public function testAnUnconfiguredOptionGivesItsDefault(): void
    {
        $service = service(MockServiceWithDefaultInjection::class);

        $this->assertSame(MockOptionWithDefault::DEFAULT, $service->value);
    }

    // The path and default are taken over when the plan is compiled, so the option and its
    // groups need not be created per request; their files are named so a change is noticed
    public function testTheCompiledValueCarriesThePathDefaultAndFiles(): void
    {
        $compiled = $this->compile(MockServiceWithDefaultInjection::class);

        $this->assertFalse($compiled->readsThroughOption);
        $this->assertSame('mock-group.with-default', $compiled->path);
        $this->assertTrue($compiled->hasDefault);
        $this->assertSame(MockOptionWithDefault::DEFAULT, $compiled->default);

        $this->assertEqualsCanonicalizing([
            new \ReflectionClass(MockOptionWithDefault::class)->getFileName(),
            new \ReflectionClass(MockConfigGroup::class)->getFileName(),
        ], $compiled->sourceFiles());
    }

    // The configured project is "service-manager"; only the option turns it into capitals
    public function testAnOptionThatTransformsItsValueIsStillAskedForIt(): void
    {
        $this->assertTrue($this->compile(MockServiceWithUppercasedInjection::class)->readsThroughOption);

        $this->assertSame(
            'SERVICE-MANAGER',
            service(MockServiceWithUppercasedInjection::class)->value
        );
    }

    private function compile(string $serviceClass): CompiledConfigValue
    {
        $parameter = new \ReflectionMethod($serviceClass, '__construct')->getParameters()[0];

        return service(ConfigOptionResolver::class)->compile($parameter);
    }
}
