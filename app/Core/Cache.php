<?php

namespace Vilare\Core;

class Cache
{
    /**
     * @action vilare_deactivate
     */
    public function clear(): void
    {
        vilare()->filesystem()->deleteDirectory(vilare()->config()->get('cache.path'));
    }

    /**
     * @action vilare_activate
     */
    public function init(): void
    {
        vilare()->filesystem()->ensureDirectoryExists(vilare()->config()->get('cache.path'));
    }
}
