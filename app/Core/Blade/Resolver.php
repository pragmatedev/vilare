<?php

namespace Vilare\Core\Blade;

class Resolver
{
    /**
     * @filter 404_template_hierarchy
     * @filter archive_template_hierarchy
     * @filter attachment_template_hierarchy
     * @filter author_template_hierarchy
     * @filter category_template_hierarchy
     * @filter date_template_hierarchy
     * @filter embed_template_hierarchy
     * @filter frontpage_template_hierarchy
     * @filter home_template_hierarchy
     * @filter index_template_hierarchy
     * @filter page_template_hierarchy
     * @filter paged_template_hierarchy
     * @filter privacypolicy_template_hierarchy
     * @filter search_template_hierarchy
     * @filter single_template_hierarchy
     * @filter singular_template_hierarchy
     * @filter tag_template_hierarchy
     * @filter taxonomy_template_hierarchy
     *
     * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/template.php#L30-L62
     */
    public function relocate(array $templates): array
    {
        if (! vilare()->config()->isTheme()) {
            return $templates;
        }

        $templates = array_map(fn($item) => preg_replace('/^[^\/]+\/|(\.blade)?\.php$/', '', $item), $templates);
        $templates = array_map(fn($item) => vilare()->config()->get('views.path') . '/' . $item . '.blade.php', $templates); // phpcs:ignore Generic.Files.LineLength.TooLong
        $templates = array_map(fn($item) => str_replace(vilare()->config()->get('resources.path') . '/', '', $item), $templates); // phpcs:ignore Generic.Files.LineLength.TooLong

        return $templates;
    }

    /**
     * @filter template_include
     */
    public function render(string $template): string
    {
        $id = get_post_meta(get_the_id(), '_wp_page_template', true);

        if (vilare()->templates()->has($id)) {
            vilare()->templates()->get($id)->render();
            return vilare()->config()->get('resources.path') . '/index.php';
        }

        if (is_singular()) {
            $type = get_post_type();

            if (vilare()->templates()->has($type) && vilare()->templates()->get($type)->isPrimary()) {
                vilare()->templates()->get($type)->render();
                return vilare()->config()->get('resources.path') . '/index.php';
            }
        }

        if (vilare()->config()->isTheme()) {
            vilare()->templating()->render($template);
            return vilare()->config()->get('resources.path') . '/index.php';
        }

        return $template;
    }
}
