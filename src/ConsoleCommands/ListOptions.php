<?php

declare(strict_types=1);

namespace Medas\ConfigOptions\ConsoleCommands;

use Medas\ConfigOptions\{
    Collection\Collector,
    Collection\OptionCollection,
    Exceptions\NoConfigValueFound,
    OptionController
};
use Medas\Console\{
    Commands\BaseConsoleCommand,
    Commands\ConsoleCommandGroup,
    Formats\Color,
    Printer,
    Text
};
use Medas\Core\{Attributes\Service, CaseInsensitiveString, Interfaces\ConfigGroup};

#[Service]
readonly class ListOptions extends BaseConsoleCommand
{
    public function __construct(
        private Collector        $collector,
        private Group            $group,
        private OptionController $optionController,
        private Printer          $printer,
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
        return 'Lists all options and default values';
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
            ->printLine(Text::create(str_repeat('  ', $depth) . $group->name() . ':', Color::LightYellow));

        foreach ($collection->options[$group::class] ?? [] as $option) {
            $this->handleDescriptionAndDefault($depth, $option);
            $this->handleNameAndValue($depth, $option);
            $this->printer->printEol();
        }

        foreach ($collection->groups[$group::class] ?? [] as $childGroup) {
            $this->processGroup($depth + 1, $childGroup, $collection);
        }
    }

    private function handleDescriptionAndDefault(int $depth, mixed $option): void
    {
        $texts[] = Text::create(
            str_repeat('  ', $depth + 1) . '# ' . $option->description(),
            Color::LightGray
        );

        if ($option->hasDefault()) {
            $defaultAsString = CaseInsensitiveString::fromVariable($option->default(), true, true);
            $texts[] = Text::create(', default: ', Color::LightGray);
            $texts[] = Text::create((string) $defaultAsString, Color::Blue);
        }

        $this->printer->printLine(...$texts);
    }

    private function handleNameAndValue(int $depth, mixed $option): void
    {
        $texts = [];
        $texts[] = Text::create(str_repeat('  ', $depth + 1) . $option->name() . ': ');

        try {
            $value = $this->optionController->getValue($option);
            $valueAsString = CaseInsensitiveString::fromVariable($value, false, true);
            $texts[] = Text::create((string) $valueAsString);
        }
        catch (NoConfigValueFound) {
            $texts[] = Text::create('no value found', Color::LightRed);
        }

        $this->printer->printLine(...$texts);
    }

    public function aliases(): array
    {
        return ['options'];
    }
}
