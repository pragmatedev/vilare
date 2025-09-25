<?php

namespace Vilare\Core\Templates;

use Vilare\Core\Templates\Template;

class Templates
{
    private array $templates = [];

    /**
     * @action after_setup_theme
     */
    public function init(): void
    {
        $classes = collect(vilare()->filesystem()->glob(VILARE_PATH . '/app/Templates/*.php'))
            ->map(fn($path) => pathinfo($path, PATHINFO_FILENAME))
            ->map(fn($name) => sprintf('Vilare\Templates\\%s', $name));

        foreach ($classes as $class) {
            $template = \Vilare\App::init(new $class());
            $this->templates[$template->getId()] = $template;
        }
    }

    public function has(string $template): bool
    {
        return isset($this->templates[$template]);
    }

    public function get(string $template): Template
    {
        return $this->templates[$template];
    }

    /**
     * @filter theme_templates
     */
    public function add(array $templates): array
    {
        if (empty($this->templates)) {
            return $templates;
        }

        foreach ($this->templates as $template) {
            $templates[$template->getId()] = $template->getTitle();
        }

        asort($templates);

        return $templates;
    }
}
