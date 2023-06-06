<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\Functional;

use Medas\ConfigOptions\ConsoleCommands\ListOptions;
use PHPUnit\Framework\TestCase;

class ListOptionsTest extends TestCase
{
    public function testOutput(): void
    {
        ob_start();
        service(ListOptions::class)->process([]);
        $output = ob_get_clean();

        self::assertStringContainsString('embedded-option: ["á", "í"]', $output);
    }
}
