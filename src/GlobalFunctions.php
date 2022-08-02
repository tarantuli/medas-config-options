<?php

declare(strict_types=1);

// This file should be in the global namespace

use Medas\ConfigOptions\ConfigOption;
use Medas\ConfigOptions\OptionController;

function option(ConfigOption $option): mixed
{
    return service(OptionController::class)->getValue($option);
}
