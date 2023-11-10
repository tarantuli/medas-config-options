<?php

declare(strict_types=1);

namespace Medas\ConfigOptions;

use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigManager,
    Interfaces\ConfigOption,
    Interfaces\Serializer,
    Interfaces\Validator
};

#[Service]
readonly class OptionController
{
    private \SplObjectStorage $values;

    public function __construct(
        private ConfigManager $configManager,
    )
    {
        $this->values = new \SplObjectStorage();
    }

    public function getValue(ConfigOption $option): mixed
    {
        if ($this->values->contains($option)) {
            return $this->values[$option];
        }

        $path = $this->getPath($option);

        if ($this->configManager->hasValue($path)) {
            $value = $this->configManager->getValue($path);

            if ($option instanceof Validator && !$option->isValid($value)) {
                /** @noinspection PhpParamsInspection $option is most certainly also a ConfigOption */
                throw new Exceptions\ValueDoesNotPassValidator($value, $option);
            }

            if ($option instanceof Serializer) {
                $value = $option->unserialize($value);
            }

            $this->values->attach($option, $value);

            return $value;
        }
        elseif ($option->hasDefault()) {
            $this->values->attach($option, $option->default());

            return $option->default();
        }

        throw new Exceptions\NoConfigValueFound($option);
    }

    public function getPath(ConfigOption $option): string
    {
        $path = $option->name();
        $group = $option->group();

        do {
            $path = $group->name() . '.' . $path;
        } while ($group = $group->parent());

        return $path;
    }

    public function hasValue(ConfigOption $option): bool
    {
        return $option->hasDefault() || $this->configManager->hasValue($this->getPath($option));
    }
}
