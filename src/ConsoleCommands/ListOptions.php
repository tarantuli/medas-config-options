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
    Commands\Argument,
    Commands\BaseConsoleCommand,
    Commands\CommandInput,
    Commands\ConsoleCommandGroup,
    Formats\SafeColor,
    Printer,
    Text
};
use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption,
    StringMaker,
    StringMaker\Settings
};

#[Service]
readonly class ListOptions extends BaseConsoleCommand
{
    private Settings $stringMakerSettings;

    public function __construct(
        private Collector        $collector,
        private Group            $group,
        private OptionController $optionController,
        private Printer          $printer,
    )
    {
        $this->stringMakerSettings = new Settings(
            quotesOnlyAroundWhitespace: true,
            forceUtf8: true,
            alwaysAddClass: true,
            useObjectIds: false,
        );
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

    public function arguments(): array
    {
        return [Argument::optional('filter', description: 'Filter options by name or description')];
    }

    public function process(CommandInput $input): void
    {
        $collection = $this->collector->collect();

        foreach ($collection->rootGroups() as $group) {
            $this->processGroup(0, $group, $collection, $input);
        }
    }

    private function processGroup(
        int              $depth,
        ConfigGroup      $group,
        OptionCollection $collection,
        CommandInput     $input
    ): void
    {
        $this->printer
            ->printLine(Text::create(str_repeat('  ', $depth) . $group->name() . ':', SafeColor::LightYellow));

        foreach ($collection->options[$group::class] ?? [] as $option) {
            if ($input->hasArgument('filter') && !$this->matchesFilter($option, $input->getArgument('filter'))) {
                continue;
            }

            $this->handleDescriptionAndDefault($depth, $option);
            $this->handleNameAndValue($depth, $option);
            $this->printer->printEol();
        }

        foreach ($collection->groups[$group::class] ?? [] as $childGroup) {
            $this->processGroup($depth + 1, $childGroup, $collection, $input);
        }
    }

    private function matchesFilter(ConfigOption $option, string $filter): bool
    {
        if (str_contains($option->name(), $filter)) {
            return true;
        }

        if (str_contains($option->description(), $filter)) {
            return true;
        }

        return false;
    }

    private function handleDescriptionAndDefault(int $depth, ConfigOption $option): void
    {
        $texts = [];
        $description = $this->compileDescription($option, $depth);
        $texts[] = Text::create($description, SafeColor::Red);

        if ($option->hasDefault()) {
            $defaultAsString = StringMaker::instance()->fromVariable(
                $option->default(),
                $this->stringMakerSettings
            );

            $texts[] = Text::create(', default: ', SafeColor::Red);
            $texts[] = Text::create($defaultAsString, SafeColor::Yellow);
        }

        $this->printer->printLine(...$texts);
    }

    private function compileDescription(ConfigOption $option, int $depth): string
    {
        $paragraphs = explode("\n", $option->description());
        $description = '';

        foreach ($paragraphs as $paragraph) {
            $indent = str_repeat('  ', $depth + 1) . '# ';
            $availableWidth = 78 - mb_strlen($indent);
            $words = preg_split('/\s+/', trim($paragraph), flags: PREG_SPLIT_NO_EMPTY);
            $lines = [];
            $currentLine = '';

            foreach ($words as $word) {
                if ($currentLine === '') {
                    $currentLine = $word;
                }
                elseif (mb_strlen($currentLine) + 1 + mb_strlen($word) <= $availableWidth) {
                    $currentLine .= ' ' . $word;
                }
                else {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                }
            }

            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }

            foreach ($lines as $line) {
                $description .= $indent . $line . "\n";
            }
        }

        return rtrim($description);
    }

    private function handleNameAndValue(int $depth, ConfigOption $option): void
    {
        $texts = [];

        $texts[] = Text::create(
            str_repeat('  ', $depth + 1) . $option->name() . ': ',
            SafeColor::LightGray
        );

        try {
            $value = $this->optionController->getValue($option);

            $valueAsString = StringMaker::instance()->fromVariable(
                $value,
                $this->stringMakerSettings
            );

            $texts[] = Text::create($valueAsString, SafeColor::Green);
        }
        catch (NoConfigValueFound) {
            $texts[] = Text::create('no value found', SafeColor::LightRed);
        }

        $this->printer->printLine(...$texts);
    }

    public function aliases(): array
    {
        return ['options'];
    }
}
