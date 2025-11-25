<?php

namespace Vilare\Core\Components;

use Vilare\Core\Components\Component;

class Components
{
    private array $components = [];

    /**
     * @action after_setup_theme
     */
    public function init(): void
    {
        $classes = collect(vilare()->filesystem()->glob(VILARE_PATH . '/app/Components/*.php'))
            ->map(fn($path) => pathinfo($path, PATHINFO_FILENAME))
            ->map(fn($name) => sprintf('Vilare\Components\\%s', $name));

        foreach ($classes as $class) {
            $component = \Vilare\App::init(new $class());
            $this->components[$component->getId()] = $component;
        }
    }

    public function has(string $key): bool
    {
        return ! empty($this->components[$key]);
    }

    public function get(string $key): ?Component
    {
        return ! empty($this->components[$key]) ? $this->components[$key] : null;
    }

    public function all(): array
    {
        return $this->components;
    }

    /**
     * @action vilare_templating_provider_init
     */
    public function register(\Illuminate\View\Compilers\BladeCompiler $compiler): void
    {
        foreach ($this->components as $component) {
            $compiler->component($component->getId(), $component::class);
        }
    }
}
