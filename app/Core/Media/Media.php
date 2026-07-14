<?php

namespace Vilare\Core\Media;

use Vilare\Core\Media\Sizes;
use Vilare\Core\Media\WEBP;

class Media
{
    public function __construct()
    {
        \Vilare\App::init(new Sizes());
        \Vilare\App::init(new WEBP());
    }

    public function getSizes(): array
    {
        $sizes = [];
        $custom = wp_get_additional_image_sizes();

        foreach (get_intermediate_image_sizes() as $size) {
            $temp = [
                'width' => intval(get_option("{$size}_size_w")),
                'height' => intval(get_option("{$size}_size_h")),
            ];

            if ((empty($temp['width']) || empty($temp['height'])) && isset($custom[$size])) {
                $temp['width'] = $custom[$size]['width'];
                $temp['height'] = $custom[$size]['height'];
            }

            $sizes[$size] = $temp;
        }

        return $sizes;
    }
}
