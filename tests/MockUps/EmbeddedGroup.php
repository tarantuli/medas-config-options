<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\ConfigGroup;

#[Service]
readonly class EmbeddedGroup implements ConfigGroup
{
    public function __construct(
        private readonly MockConfigGroup $parent,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->parent;
    }

    public function name(): string
    {
        return 'embedded-group';
    }
}
