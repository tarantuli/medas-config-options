<?php

declare(strict_types=1);

// This file should be in the global namespace

use Medas\ConfigOptions\OptionController;
use Medas\ServiceManager\ConfigOptions\ConfigOption;

function option(ConfigOption $option): mixed
{
    return service(OptionController::class)->getValue($option);
}
