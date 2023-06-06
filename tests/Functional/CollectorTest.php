<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\Functional;

use Medas\ConfigOptions\Collection\Collector;
use Medas\ConfigOptionsTest\MockUps\MockConfigGroup;
use Medas\CoreTest\BaseTest;

class CollectorTest extends BaseTest
{
    public function testCollector(): void
    {
        $result = service(Collector::class)->collect();

        self::assertArrayHasKey(0, $result->groups);
        self::assertArrayHasKey(MockConfigGroup::class, $result->groups);
    }
}
