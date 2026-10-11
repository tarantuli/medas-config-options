<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

/**
 * The compiled form of a parameter tagged with @class(Medas\Core\Attributes\ConfigValue): the config option class,
 * plus the declared types, which the string value from the configuration is converted to.
 */
readonly class CompiledConfigValue
{
    /**
     * @param list<array{string, bool}> $types Type name and whether the type allows null
     */
    public function __construct(
        public string $configOption,
        public array  $types,
    )
    {
    }
}
