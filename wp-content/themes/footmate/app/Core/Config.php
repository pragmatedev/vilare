<?php

namespace FM\Core;

class Config
{
    private array $config = [];

    public function __construct()
    {
        $this->config = [
            'version' => wp_get_environment_type() === 'development' ? time() : FM_VERSION,
            'env' => [
                'type' => wp_get_environment_type(),
                'mode' => false === strpos(FM_PATH, ABSPATH . 'wp-content/plugins') ? 'theme' : 'plugin',
            ],
            'hmr' => [
                'uri' => FM_HMR_HOST,
                'client' => FM_HMR_URI . '/@vite/client',
                'resources' => FM_HMR_URI . '/resources',
                'active' => wp_get_environment_type() === 'development' && ! is_wp_error(wp_remote_get(FM_HMR_URI)),
            ],
            'manifest' => [
                'path' => FM_DIST_PATH . '/manifest.json',
            ],
            'cache' => [
                'path' => WP_CONTENT_DIR . '/cache/footmate',
            ],
            'dist' => [
                'path' => FM_DIST_PATH,
                'uri' => FM_DIST_URI,
            ],
            'resources' => [
                'path' => FM_PATH . '/resources',
            ],
            'views' => [
                'path' => FM_PATH . '/resources/views',
            ],
            'blocks' => [
                'path' => FM_PATH . '/resources/blocks',
            ],
            'components' => [
                'path' => FM_PATH . '/resources/components',
            ],
            'templates' => [
                'path' => FM_PATH . '/resources/templates',
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
