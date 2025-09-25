<?php

namespace Vilare\Core\Integrations;

use Vilare\Core\Integrations\ACF;
use Vilare\Core\Integrations\Vite;

class Integrations
{
    /**
     * @action init 1
     */
    public function init(): void
    {
        $plugins = apply_filters('active_plugins', get_option('active_plugins'));

        if (vilare()->config()->get('hmr.active')) {
            \Vilare\App::init(new Vite());
        }

        if (in_array('advanced-custom-fields-pro/acf.php', $plugins)) {
            \Vilare\App::init(new ACF());
        }
    }
}
