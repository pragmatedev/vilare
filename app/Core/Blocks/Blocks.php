<?php

namespace Vilare\Core\Blocks;

use Vilare\Core\Blocks\Block;

class Blocks
{
    private array $blocks = [];

    /**
     * @action after_setup_theme 1
     */
    public function init(): void
    {
        $classes = collect(vilare()->filesystem()->glob(VILARE_PATH . '/app/Blocks/*.php'))
            ->map(fn($path) => pathinfo($path, PATHINFO_FILENAME))
            ->map(fn($name) => sprintf('Vilare\Blocks\\%s', $name));

        foreach ($classes as $class) {
            $block = \Vilare\App::init(new $class());
            $this->blocks[$block->getId()] = $block;
        }
    }

    public function has(string $key): bool
    {
        return ! empty($this->blocks[$key]);
    }

    public function get(string $key): ?Block
    {
        return ! empty($this->blocks[$key]) ? $this->blocks[$key] : null;
    }

    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * @action wp_enqueue_scripts
     */
    public function enqueue(): void
    {
        foreach ($this->blocks as $block) {
            if ($block->isPrimary()) {
                $block->enqueue();
            }
        }
    }

    /**
     * @filter should_load_block_assets_on_demand
     */
    public function defer(): bool
    {
        return true;
    }
}
