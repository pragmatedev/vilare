<?php

namespace FM\Components;

use Illuminate\View\Compilers\BladeCompiler;

class Components
{
    private array $components = [];

    /**
     * @action after_setup_theme
     */
    public function init(): void
    {
        $classes = collect(fm()->filesystem()->glob(__DIR__ . '/*.php'))
            ->map(fn($path) => pathinfo($path, PATHINFO_FILENAME))
            ->reject(fn($name) => in_array($name, ['Component', 'Components']))
            ->map(fn($name) => sprintf('FM\Components\\%s', $name));

        foreach ($classes as $class) {
            $component = \FM\App::init(new $class());
            $this->components[$component->getId()] = $component;
        }
    }

    /**
     * @action fm_templating_provider_init
     */
    public function register(BladeCompiler $compiler): void
    {
        foreach ($this->components as $component) {
            $compiler->component($component->getId(), $component::class);
        }
    }
}
