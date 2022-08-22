<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\ConfigValue;

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
