<?php

namespace Vilare\Core\Plugins;

use Vilare\Core\Plugins\Plugin;

class Plugins
{
    private array $plugins = [];

    /**
     * @action after_setup_theme 1
     */
    public function init(): void
    {
        $classes = collect(vilare()->filesystem()->glob(VILARE_PATH . '/app/Plugins/*.php'))
            ->map(fn($path) => pathinfo($path, PATHINFO_FILENAME))
            ->map(fn($name) => sprintf('Vilare\Plugins\\%s', $name));

        foreach ($classes as $class) {
            $plugin = \Vilare\App::init(new $class());
            $this->plugins[$plugin->getId()] = $plugin;
        }
    }

    public function has(string $key): bool
    {
        return ! empty($this->plugins[$key]);
    }

    public function get(string $key): ?Plugin
    {
        return ! empty($this->plugins[$key]) ? $this->plugins[$key] : null;
    }

    public function all(): array
    {
        return $this->plugins;
    }
}
