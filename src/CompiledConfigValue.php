<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\Core\Interfaces\DependsOnSourceFiles;

/**
 * The compiled form of a parameter tagged with @class(Medas\Core\Attributes\ConfigValue): the config option class,
 * plus the declared types, which the string value from the configuration is converted to.
 *
 * The option's path and default are taken over from the option when the plan is compiled, so the value can be read
 * without creating the option and its groups on every request. An option with behaviour of its own (it validates or
 * unserializes its value, or its default can't be stored) is still asked at run time: see $readsThroughOption.
 */
readonly class CompiledConfigValue implements DependsOnSourceFiles
{
    /**
     * @param list<array{string, bool}> $types Type name and whether the type allows null
     * @param string[] $sourceFiles The option's and its groups' files, so a changed name or default is picked up
     */
    public function __construct(
        public string $configOption,
        public array  $types,
        public bool   $readsThroughOption,
        public string $path,
        public bool   $hasDefault,
        public mixed  $default,
        private array $sourceFiles,
    )
    {
    }

    public function sourceFiles(): array
    {
        return $this->sourceFiles;
    }
}
