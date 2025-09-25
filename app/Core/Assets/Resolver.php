<?php

namespace Vilare\Core\Assets;

trait Resolver
{
    private array $manifest = [];

    /**
     * @action init
     */
    public function load(): void
    {
        $path = vilare()->config()->get('manifest.path');

        if (empty($path) || ! file_exists($path)) {
            wp_die('Run <code>yarn build</code> in your application root!');
        }

        $data = vilare()->filesystem()->get($path);

        if (! empty($data)) {
            $this->manifest = json_decode($data, true);
        }
    }

    /**
     * @filter script_loader_tag 1 3
     */
    public function module(string $tag, string $handle, string $url): string
    {
        if (
            false !== strpos($url, vilare()->config()->get('hmr.uri')) ||
            false !== strpos($url, vilare()->config()->get('dist.uri'))
        ) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }

        return $tag;
    }

    public function enqueue(string $path, array $config = []): string
    {
        $config = [
            'handle' => ! empty($config['handle']) ? $config['handle'] : uniqid(),
            'src' => ! empty($path) ? $this->resolve($path) : $path,
            'deps' => ! empty($config['deps']) ? $config['deps'] : [],
            'version' => ! empty($config['version']) ? $config['version'] : vilare()->config()->get('version'),
            'args' => [
                'strategy' => ! empty($config['strategy']) ? $config['strategy'] : null,
                'footer' => isset($config['footer']) ? (bool) $config['footer'] : true,
                'media' => ! empty($config['media']) ? $config['media'] : 'all',
            ],
            'type' => ! empty($config['type']) ? $config['type'] : '',
        ];

        if (preg_match('/\.(css|scss)(\?.*)?$/', $path)) {
            $config['type'] = 'style';
        } elseif (preg_match('/\.js(\?.*)?$/', $path)) {
            $config['type'] = 'script';
        }

        if (! filter_var($path, FILTER_VALIDATE_URL)) {
            $dependencies = $this->dependencies($path);

            if (! empty($dependencies['scripts'])) {
                foreach ($dependencies['scripts'] as $index => $script) {
                    $config['deps'][] = $this->enqueue(
                        $script,
                        [
                            'handle' => "{$config['handle']}-{$index}",
                        ]
                    );
                }
            }

            if (! empty($dependencies['styles'])) {
                foreach ($dependencies['styles'] as $index => $style) {
                    $config['deps'][] = $this->enqueue(
                        $style,
                        [
                            'handle' => "{$config['handle']}-{$index}",
                        ]
                    );
                }
            }
        }

        switch ($config['type']) {
            case 'script':
                wp_enqueue_script(
                    $config['handle'],
                    $config['src'],
                    $config['deps'],
                    $config['version'],
                    [
                        'strategy' => $config['args']['strategy'],
                        'in_footer' => $config['args']['footer'],
                    ]
                );
                wp_set_script_translations($config['handle'], 'vilare');
                break;

            case 'style':
                wp_enqueue_style(
                    $config['handle'],
                    $config['src'],
                    $config['deps'],
                    $config['version'],
                    $config['args']['media']
                );
                break;
        }

        return $config['handle'];
    }

    public function resolve(string $path, string $type = 'url'): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $data = $this->find($path);

        switch ($type) {
            case 'url':
                $url = ! empty($data['file']) ? vilare()->config()->get('dist.uri') . "/{$data['file']}" : '';

                return apply_filters('vilare_assets_resolver_resolve_url', $url, $path);

            case 'path':
                $fullpath = ! empty($data['file']) ? vilare()->config()->get('dist.path') . "/{$data['file']}" : '';

                return apply_filters('vilare_assets_resolver_resolve_path', $fullpath, $path);
        }

        return '';
    }

    public function dependencies(string $path): array
    {
        $assets = [
            'scripts' => [],
            'styles' => [],
        ];

        if (vilare()->config()->get('hmr.active')) {
            return $assets;
        }

        if ($this->has($path)) {
            $assets['scripts'] = collect($this->find($path)['js'] ?? [])
                ->map(fn($item) => vilare()->config()->get('dist.uri') . '/' . $item)
                ->all();

            $assets['styles'] = collect($this->find($path)['css'] ?? [])
                ->map(fn($item) => vilare()->config()->get('dist.uri') . '/' . $item)
                ->all();
        }

        return $assets;
    }

    private function find(string $path): array
    {
        return $this->has($path) ? $this->manifest["resources/{$path}"] : [];
    }

    private function has(string $path): bool
    {
        return ! empty($this->manifest["resources/{$path}"]);
    }
}
