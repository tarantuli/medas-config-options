<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption,
    Interfaces\Serializer,
    Interfaces\Type
};

// Reads the configured project and turns it into capitals: only the option itself can do
// that, so the value has to be read through it
#[Service]
readonly class MockUppercasingOption implements ConfigOption, Serializer
{
    public function __construct(
        private MockConfigGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'project';
    }

    public function description(): string
    {
        return 'The project, in capitals';
    }

    public function hasDefault(): bool
    {
        return false;
    }

    public function default(): mixed
    {
        return null;
    }

    public function serialize(mixed $value): mixed
    {
        return strtolower($value);
    }

    public function unserialize(mixed $value, Type|null $type = null): mixed
    {
        return strtoupper($value);
    }
}
