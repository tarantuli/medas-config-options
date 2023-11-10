<?php

declare(strict_types=1);

namespace Medas\ConfigOptions\Collection;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption, Interfaces\ImplementorFinder};

#[Service]
readonly class Collector
{
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
        $configGroups = service(ImplementorFinder::class)->find(ConfigGroup::class);

        foreach ($configGroups as $configGroup) {
            $parent = $configGroup->parent() ? $configGroup->parent()::class : 0;

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
        $configOptions = service(ImplementorFinder::class)->find(ConfigOption::class);

        foreach ($configOptions as $configOption) {
            $group = $configOption->group()::class;

            if (!array_key_exists($group, $options)) {
                $options[$group] = [];
            }

            $options[$group][] = $configOption;
        }

        return $options;
    }
}
