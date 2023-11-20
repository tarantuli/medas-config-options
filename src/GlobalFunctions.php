<?php

declare(strict_types=1);

use Medas\ConfigOptions\OptionController;
use Medas\Core\Interfaces\ConfigOption;

// This file should be in the global namespace
function option(ConfigOption $option): mixed
{
    return service(OptionController::class)->getValue($option);
}
