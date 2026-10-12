<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

// Not set in config.yaml, so the default applies
#[Service]
readonly class MockOptionWithDefault implements ConfigOption
{
    public const string DEFAULT = 'the default';

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
        return 'with-default';
    }

    public function description(): string
    {
        return 'An option that is not configured';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): mixed
    {
        return self::DEFAULT;
    }
}
