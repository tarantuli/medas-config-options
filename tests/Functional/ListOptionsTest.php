<?php

declare(strict_types=1);

namespace Medas\ConfigOptionsTest\Functional;

use Medas\ConfigOptions\ConsoleCommands\ListOptions;
use Medas\Console\Commands\CommandInput;
use PHPUnit\Framework\TestCase;

class ListOptionsTest extends TestCase
{
    public function testOutput(): void
    {
        ob_start();

        try {
            service(ListOptions::class)->process(new CommandInput());
        }

        finally{
            $output = ob_get_clean();
        }

        // Without the colour codes, which are not what this test is about
        $output = preg_replace('/\e\[[0-9;]*m/', '', $output);

        self::assertStringContainsString('embedded-option', $output);
        self::assertMatchesRegularExpression('/default: .*á.*í/u', $output);
    }
}
