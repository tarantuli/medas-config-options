<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};
use Medas\Core\AsSingleton;

class MockConfigOption implements ConfigOption
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return MockConfigGroup::instance();
    }

    public function name(): string
    {
        return 'project';
    }

    public function description(): string
    {
        return 'The project';
    }

    public function isValid(mixed $value): bool
    {
        return is_string($value);
    }

    public function hasDefault(): bool
    {
        return false;
    }

    public function default(): mixed
    {
        return null;
    }
}
