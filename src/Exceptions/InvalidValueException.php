<?php

declare(strict_types=1);

namespace Medas\ConfigOptions\Exceptions;

use Medas\ConfigOptions\ConfigOption;
use Medas\Core\Exceptions\BaseException;

class InvalidValueException extends BaseException
{
    public function __construct(string $value, ConfigOption $configOption)
    {
        parent::__construct($value, $configOption::class);
    }

    public function pattern(): string
    {
        return 'Invalid value "%s" found for config option %s';
    }
}
