<?php

namespace Vilare\Core;

class Config
{
    private array $config = [];

    public function __construct()
    {
        $this->config = [
            'version' => wp_get_environment_type() === 'development' ? time() : VILARE_VERSION,
            'env' => [
                'type' => wp_get_environment_type(),
                'mode' => false === strpos(VILARE_PATH, ABSPATH . 'wp-content/plugins') ? 'theme' : 'plugin',
            ],
            'hmr' => [
                'uri' => VILARE_HMR_HOST,
                'client' => VILARE_HMR_URI . '/@vite/client',
                'resources' => VILARE_HMR_URI . '/resources',
                'active' => wp_get_environment_type() === 'development' && ! is_wp_error(wp_remote_get(VILARE_HMR_URI)),
            ],
            'manifest' => [
                'path' => VILARE_DIST_PATH . '/manifest.json',
            ],
            'cache' => [
                'path' => WP_CONTENT_DIR . '/cache/vilare',
            ],
            'dist' => [
                'path' => VILARE_DIST_PATH,
                'uri' => VILARE_DIST_URI,
            ],
            'resources' => [
                'path' => VILARE_PATH . '/resources',
            ],
            'views' => [
                'path' => VILARE_PATH . '/resources/views',
            ],
            'blocks' => [
                'path' => VILARE_PATH . '/resources/blocks',
            ],
            'components' => [
                'path' => VILARE_PATH . '/resources/components',
            ],
            'templates' => [
                'path' => VILARE_PATH . '/resources/templates',
            ],
        ];
    }

    public function get(string $key): mixed
    {
        return data_get($this->config, $key);
    }

    public function isTheme(): bool
    {
        return 'theme' === $this->get('env.mode');
    }

    public function isPlugin(): bool
    {
        return 'plugin' === $this->get('env.mode');
    }
}
