<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\Attributes\{ConfigValue, Service};

#[Service]
readonly class MockServiceWithConfigInjection
{
    public function __construct(
        #[ConfigValue(MockConfigOption::class)] private string $project,
    )
    {
    }

    public function getProject(): string
    {
        return $this->project;
    }
}
