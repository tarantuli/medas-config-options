<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\BasePackage;

class MockPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
