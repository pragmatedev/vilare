<?php

namespace Vilare\Core\Integrations;

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

        foreach (vilare()->blocks()->all() as $block) {
            acf_register_block_type(
                [
                    'name'  => $block->getId(),
                    'title' => $block->getTitle(),
                    'keywords' => ['vilare'],
                    'supports' => [
                        'anchor' => true,
                        'jsx' => $block->usesInnerBlocks(),
                    ],
                    'mode' => $block->usesInnerBlocks() ? 'preview' : 'edit',
                    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
                    'render_callback' => function ($config, $content, $preview, $post) use ($block) {
                        $block->render(
                            array_merge(
                                get_fields() ?: [],
                                [
                                    'is_preview' => $preview,
                                    'inner_blocks' => $block->getInnerBlocks(),
                                    'attributes' => [
                                        'id' => ! empty($config['anchor']) ? $config['anchor'] : '',
                                        'class' => ! empty($config['className']) ? $config['className'] : '',
                                    ],
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
        if (preg_match('/\[Vilare\]/', $post['title'])) {
            return [VILARE_PATH . '/resources/fields'];
        }

        return $paths;
    }

    /**
     * @filter acf/settings/load_json
     */
    public function load(array $paths): array
    {
        $paths[] = VILARE_PATH . '/resources/fields';

        return $paths;
    }
}
