<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};

#[Service]
readonly class EmbeddedOption implements ConfigOption
{
    public function __construct(
        private readonly EmbeddedGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'embedded-option';
    }

    public function description(): string
    {
        return 'An embedded option';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): array
    {
        return ['á', 'í'];
    }
}
