<?php

namespace Vilare;

use Vilare\Core\Assets\Resolver;

class Assets
{
    use Resolver;

    /**
     * @action wp_enqueue_scripts
     */
    public function front(): void
    {
        $this->enqueue(
            'scripts/alpine.js',
            [
                'handle' => 'alpine',
            ]
        );

        $this->enqueue(
            'styles/styles.scss',
            [
                'handle' => 'style',
            ]
        );

        $this->enqueue(
            'scripts/scripts.js',
            [
                'handle' => 'script',
                'deps' => ['alpine'],
            ]
        );

        wp_localize_script(
            'script',
            'vilare',
            apply_filters(
                'vilare_assets_localize',
                [
                    'ajax' => admin_url('admin-ajax.php'),
                ]
            )
        );

        wp_add_inline_style('style', 'body { [x-cloak] { display: none } }');
    }

    /**
     * @action admin_enqueue_scripts
     */
    public function admin(): void
    {
        $this->enqueue('styles/admin.scss', ['handle' => 'admin']);

        if ('post' === get_current_screen()->base && has_blocks()) {
            $this->front();
        }
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
                ],
                [
                    'href' => vilare()->assets()->resolve('fonts/SourceSans3.woff2'),
                    'as' => 'font',
                    'type' => 'font/woff2',
                ],
            ]
        );

        foreach ($preloads as $item) {
            if (empty($item['href']) || empty($item['as']) || empty($item['type'])) {
                continue;
            }

            printf(
                '<link rel="preload" href="%s" as="%s" type="%s" crossorigin="true" />',
                esc_attr($item['href']),
                esc_attr($item['as']),
                esc_attr($item['type']),
            );
        }
    }
}
