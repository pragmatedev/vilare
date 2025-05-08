<?php

namespace FM\Templates;

class Templates
{
    private array $templates = [];

    /**
     * @action after_setup_theme
     */
    public function init(): void
    {
        $classes = collect(fm()->filesystem()->glob(__DIR__ . '/*.php'))
            ->map(fn($path) => pathinfo($path, PATHINFO_FILENAME))
            ->reject(fn($name) => in_array($name, ['Template', 'Templates']))
            ->map(fn($name) => sprintf('FM\Templates\\%s', $name));

        foreach ($classes as $class) {
            $template = \FM\App::init(new $class());
            $this->templates[$template->getId()] = $template;
        }
    }

    /**
     * @filter theme_page_templates
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

    /**
     * @filter template_include 9999
     */
    public function render(string $path): string
    {
        $template = get_post_meta(get_the_id(), '_wp_page_template', true);

        if (! empty($template) && ! empty($this->templates[$template])) {
            $this->templates[$template]->render();

            return fm()->config()->get('resources.path') . '/index.php';
        }

        return $path;
    }
}
