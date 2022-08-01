<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\ConfigOptions\ConfigGroup;
use Medas\ServiceManager\AsSingleton;

class MockConfigGroup implements ConfigGroup
{
    use AsSingleton;

    public function parent(): ConfigGroup|null
    {
        return null;
    }

    public function name(): string
    {
        return 'mock-group';
    }
}
