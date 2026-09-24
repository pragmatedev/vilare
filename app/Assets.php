<?php

namespace Vilare;

use Vilare\Core\Assets\Resolver;

class Assets
{
    use Resolver;

    /**
     * @action init 5
     */
    public function init(): void
    {
        $this->register(
            'scripts/alpine.js',
            [
                'handle' => 'alpine',
                'strategy' => 'defer',
                'footer' => true,
            ]
        );

        $this->register(
            is_admin() ? 'styles/admin.scss' : 'styles/styles.scss',
            [
                'handle' => 'style',
            ]
        );

        $this->register(
            is_admin() ? 'scripts/admin.js' : 'scripts/scripts.js',
            [
                'handle' => 'script',
                'deps' => is_admin()
                    ? ['alpine', 'wp-block-editor', 'wp-components', 'wp-hooks', 'wp-i18n']
                    : ['alpine'],
            ]
        );

        wp_localize_script(
            'script',
            'vilare',
            apply_filters(
                'vilare_assets_localize',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('vilare'),
                ]
            )
        );

        wp_add_inline_style('style', 'body { [x-cloak] { display: none !important } }');
    }

    /**
     * @action wp_enqueue_scripts
     * @action admin_enqueue_scripts
     */
    public function theme(): void
    {
        wp_enqueue_style('style');
        wp_enqueue_script('script');
    }

    /**
     * @action wp_head
     */
    public function preload(): void
    {
        $preloads = apply_filters(
            'vilare_assets_preload',
            [
                [
                    'href' => vilare()->assets()->resolve('fonts/Montserrat.woff2'),
                    'as' => 'font',
                    'type' => 'font/woff2',
                    'fetchpriority' => 'high',
                ],
                [
                    'href' => vilare()->assets()->resolve('fonts/SourceSans3.woff2'),
                    'as' => 'font',
                    'type' => 'font/woff2',
                    'fetchpriority' => 'high',
                ],
            ]
        );

        foreach ($preloads as $item) {
            if (empty($item['href']) || empty($item['as']) || empty($item['type'])) {
                continue;
            }

            printf(
                '<link rel="preload" href="%s" as="%s" type="%s" crossorigin fetchpriority="%s" />',
                esc_attr($item['href']),
                esc_attr($item['as']),
                esc_attr($item['type']),
                esc_attr($item['fetchpriority']),
            );
        }
    }
}
