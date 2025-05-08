<?php

namespace FM;

class Cache
{
    /**
     * @action fm_deactivate
     */
    public function clear(): void
    {
        fm()->filesystem()->deleteDirectory(fm()->config()->get('cache.path'));
    }

    /**
     * @action fm_activate
     */
    public function init(): void
    {
        fm()->filesystem()->ensureDirectoryExists(fm()->config()->get('cache.path'));
    }
}
