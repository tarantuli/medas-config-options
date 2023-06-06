<?php

declare(strict_types=1);

namespace Medas\ConfigOptions\Collection;

use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};

class OptionCollection
{
    public function __construct(
        /** @var ConfigGroup[][] */
        public array $groups = [],

        /** @var ConfigOption[][] */
        public array $options = [],
    )
    {
    }
}
