<?php

declare(strict_types=1);

namespace Medas\ConfigOptions\ConsoleCommands;

use Medas\ConfigOptions\Collection\{Collector, OptionCollection};
use Medas\ConfigOptions\OptionController;
use Medas\Console\{Commands\BaseConsoleCommand, Commands\ConsoleCommandGroup, Formats\Color, Printer, Text};
use Medas\Core\Attributes\Service;
use Medas\Core\CaseInsensitiveString;
use Medas\Core\Interfaces\ConfigGroup;

#[Service]
class ListOptions extends BaseConsoleCommand
{
    public function __construct(
        private readonly Collector        $collector,
        private readonly Group            $group,
        private readonly OptionController $optionController,
        private readonly Printer          $printer,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'list';
    }

    public function description(): string
    {
        return 'list all options and default values';
    }

    public function process(array $arguments): void
    {
        $collection = $this->collector->collect();

        foreach ($collection->groups[0] as $group) {
            $this->processGroup(0, $group, $collection);
        }
    }

    private function processGroup(int $depth, ConfigGroup $group, OptionCollection $collection): void
    {
        $this->printer
            ->printLine(Text::create(str_repeat('  ', $depth) . $group->name() . ':'));

        foreach ($collection->options[$group::class] ?? [] as $option) {
            $texts[] = Text::create(str_repeat('  ', $depth + 1)
                . '# ' . $option->description()
            );

            if ($option->hasDefault()) {
                $defaultAsString = CaseInsensitiveString::fromVariable($option->default(), true, true);
                $texts[] = Text::create(', default: ');
                $texts[] = Text::create((string) $defaultAsString, Color::Blue);
            }

            $this->printer->printLine(... $texts);

            $texts = [];

            $valueAsString = CaseInsensitiveString::fromVariable($this->optionController->getValue($option), false, true);

            $texts[] = Text::create(str_repeat('  ', $depth + 1)
                . $option->name()
                . ': '
                . $valueAsString
            );

            $this->printer->printLine(... $texts);

            $this->printer
                ->printEol();
        }

        foreach ($collection->groups[$group::class] ?? [] as $childGroup) {
            $this->processGroup($depth + 1, $childGroup, $collection);
        }
    }
}
