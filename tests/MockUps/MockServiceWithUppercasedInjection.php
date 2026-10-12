<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\MockUps;

use Medas\Core\Attributes\{ConfigValue, Service};

#[Service]
readonly class MockServiceWithUppercasedInjection
{
    public function __construct(
        #[ConfigValue(MockUppercasingOption::class)]
        public string $value,
    )
    {
    }
}
