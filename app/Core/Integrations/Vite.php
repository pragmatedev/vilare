<?php

namespace Vilare\Core\Integrations;

class Vite
{
    /**
     * @action wp_head 1
     */
    public function client(): void
    {
        //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
        printf('<script type="module" src="%s"></script>', esc_attr(vilare()->config()->get('hmr.client')));
    }

    /**
     * @filter vilare_assets_resolver_resolve_url 1 2
     */
    public function url(string $current, string $path): string
    {
        return vilare()->config()->get('hmr.resources') . "/{$path}";
    }

    /**
     * @filter vilare_assets_resolver_resolve_path 1 2
     */
    public function path(string $current, string $path): string
    {
        return vilare()->config()->get('resources.path') . "/{$path}";
    }
}
