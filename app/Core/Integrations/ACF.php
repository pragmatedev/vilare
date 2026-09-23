<?php

namespace Vilare\Core\Integrations;

class ACF
{
    /**
     * @action init
     */
    public function blocks(): void
    {
        foreach (vilare()->blocks()->all() as $block) {
            $block->register();

            register_block_type(
                vilare()->config()->get('blocks.path') . "/{$block->getId()}",
                [
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
                                        'background' => ! empty($config['background']) ? $config['background'] : 'none',
                                    ],
                                ]
                            )
                        );
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
        if (! empty($post['title']) && preg_match('/\[Vilare\]/', $post['title'])) {
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
