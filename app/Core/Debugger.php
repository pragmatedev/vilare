<?php

namespace Vilare\Core;

use Spatie\Ignition\Ignition;

class Debugger
{
    public function __construct()
    {
        if (! empty(WP_DEBUG) && ! empty(WP_DEBUG_DISPLAY) && wp_get_environment_type() === 'development') {
            Ignition::make()->register();
        }
    }
}
