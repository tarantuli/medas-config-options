<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\ConfigValue;

#[Service]
class MockServiceWithInvalidConfigInjection
{
    public function __construct(
        #[ConfigValue('project')] private string $project,
    )
    {
    }

    public function project(): string
    {
        return $this->project;
    }
}
