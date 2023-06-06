<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\ConfigGroup;
use Medas\Core\Interfaces\ConfigOption;

#[Service]
class OptionWithoutDefault implements ConfigOption
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
        return 'option-without-default';
    }

    public function description(): string
    {
        return 'An option without a default value';
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
