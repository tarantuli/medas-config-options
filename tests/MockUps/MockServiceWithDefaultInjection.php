<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\Attributes\{ConfigValue, Service};

#[Service]
readonly class MockServiceWithDefaultInjection
{
    public function __construct(
        #[ConfigValue(MockOptionWithDefault::class)]
        public string $value,
    )
    {
    }
}
