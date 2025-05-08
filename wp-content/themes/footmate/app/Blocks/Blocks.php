<?php

namespace FM\Blocks;

use FM\Blocks\Block;

class Blocks
{
    private array $blocks = [];

    /**
     * @action after_setup_theme
     */
    public function init(): void
    {
        $classes = collect(fm()->filesystem()->glob(__DIR__ . '/*.php'))
            ->map(fn($path) => pathinfo($path, PATHINFO_FILENAME))
            ->reject(fn($name) => in_array($name, ['Block', 'Blocks']))
            ->map(fn($name) => sprintf('FM\Blocks\\%s', $name));

        foreach ($classes as $class) {
            $block = \FM\App::init(new $class());
            $this->blocks[$block->getId()] = $block;
        }
    }

    public function get(string $key): ?Block
    {
        return ! empty($this->blocks[$key]) ? $this->blocks[$key] : null;
    }

    public function all(): array
    {
        return $this->blocks;
    }
}
