<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\{AsSingleton, BasePackage};

class MockPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [];
    }

    // Its classes live under tests/, which has no composer.json of its own, so the
    // PSR-4 loader would find none of them: a test package is loaded file by file.
    public function isTestPackage(): bool
    {
        return true;
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
