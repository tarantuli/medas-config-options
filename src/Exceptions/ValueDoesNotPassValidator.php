<?php

declare(strict_types=1);

namespace Medas\ConfigOptions\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\Core\Interfaces\ConfigOption;

class ValueDoesNotPassValidator extends BaseException
{
    public function __construct(string $value, ConfigOption $configOption)
    {
        parent::__construct($value, $configOption::class);
    }

    public function pattern(): string
    {
        return 'Invalid value %s found for config option %s';
    }
}
