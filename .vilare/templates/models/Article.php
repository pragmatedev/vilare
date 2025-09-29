<?php

namespace Vilare;

use Vilare\Core\Entry;

class Article extends Entry
{
    public function __construct(int $id)
    {
        $post = get_post($id);

        if (empty($post)) {
            throw new \Exception(sprintf('Article %d not found', (int) $id));
        }

        $this->setId($post->ID);
    }

    public static function resolve(int $id): ?self
    {
        try {
            $instance = wp_cache_get("vilare_article_{$id}");

            if (! $instance instanceof self) {
                wp_cache_set("vilare_article_{$id}", $instance = new self($id));
            }

            return $instance;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
