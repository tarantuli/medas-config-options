<?php

declare(strict_types=1);

namespace Medas\ConfigOptions\Collection;

use Medas\Core\{
    Attributes\Service,
    CachedImplementorList,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption
};

#[Service]
readonly class Collector
{
    private CachedImplementorList $configGroups;
    private CachedImplementorList $configOptions;

    public function __construct()
    {
        $this->configGroups = new CachedImplementorList(ConfigGroup::class);
        $this->configOptions = new CachedImplementorList(ConfigOption::class);
    }

    public function collect(): OptionCollection
    {
        $groups = $this->gatherGroups();
        $options = $this->gatherOptions();

        return new OptionCollection($groups, $options);
    }

    /** @return ConfigGroup[][] */
    private function gatherGroups(): array
    {
        $groups = [];

        foreach ($this->configGroups->get() as $configGroup) {
            $parent = $configGroup->parent()
                ? $configGroup->parent()::class
                : OptionCollection::ROOT_GROUP_KEY;

            if (!array_key_exists($parent, $groups)) {
                $groups[$parent] = [];
            }

            $groups[$parent][] = $configGroup;
        }

        return $groups;
    }

    /** @return ConfigOption[][] */
    private function gatherOptions(): array
    {
        $options = [];

        foreach ($this->configOptions->get() as $configOption) {
            $group = $configOption->group()::class;

            if (!array_key_exists($group, $options)) {
                $options[$group] = [];
            }

            $options[$group][] = $configOption;
        }

        return $options;
    }
}
