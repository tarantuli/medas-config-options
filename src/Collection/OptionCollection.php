<?php

declare(strict_types=1);

namespace Medas\ConfigOptions\Collection;

use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};

class OptionCollection
{
    /** Key used in $groups for top-level groups that have no parent. */
    public const string ROOT_GROUP_KEY = '__root__';

    public function __construct(
        /** @var ConfigGroup[][] */
        public array $groups = [],

        /** @var ConfigOption[][] */
        public array $options = [],
    )
    {
    }

    /** @return ConfigGroup[] */
    public function rootGroups(): array
    {
        return $this->groups[self::ROOT_GROUP_KEY] ?? [];
    }
}
