<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\ConfigOptions\Attributes\ConfigValue;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class MockServiceWithConfigInjection
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
