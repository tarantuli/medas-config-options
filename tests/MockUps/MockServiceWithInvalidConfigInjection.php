<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\ConfigOptions\Attributes\ConfigValue;
use Medas\ServiceManager\Attributes\Service;

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
