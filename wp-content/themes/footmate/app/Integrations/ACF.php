<?php

namespace FM\Integrations;

class ACF
{
    /**
     * @action init
     */
    public function blocks(): void
    {
        if (! function_exists('acf_register_block_type')) {
            return;
        }

        foreach (fm()->blocks()->all() as $block) {
            acf_register_block_type(
                [
                    'name'  => $block->getId(),
                    'title' => $block->getTitle(),
                    'keywords' => ['footmate'],
                    'supports' => [
                        'jsx' => $block->usesInnerBlocks(),
                    ],
                    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
                    'render_callback' => function ($config, $content, $preview, $post) use ($block) {
                        $block->render(
                            array_merge(
                                get_fields() ?: [],
                                [
                                    'is_preview' => $preview,
                                    'inner_blocks' => $block->getInnerBlocks(),
                                ]
                            )
                        );
                    },
                    'enqueue_assets' => function () use ($block) {
                        $block->enqueue();
                    },
                ]
            );
        }
    }

    /**
     * @filter acf/json/save_paths
     */
    public function save(array $paths, array $post): array
    {
        if (preg_match('/\[FM\]/', $post['title'])) {
            return [FM_PATH . '/resources/fields'];
        }

        return $paths;
    }

    /**
     * @filter acf/settings/load_json
     */
    public function load(array $paths): array
    {
        $paths[] = FM_PATH . '/resources/fields';

        return $paths;
    }
}
