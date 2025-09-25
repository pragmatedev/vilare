<?php

namespace Vilare\Core\Media;

class Sizes
{
    private array $default = [];

    private array $additional = [];

    /**
     * @action after_setup_theme
     */
    public function setMediaSizes(): void
    {
        $sizes = apply_filters(
            'vilare_media_sizes',
            [
                'thumbnail' => [
                    'width' => 360,
                    'height' => 203,
                    'crop' => true,
                ],
                'medium' => [
                    'width' => 720,
                    'height' => 405,
                    'crop' => ['center', 'center'],
                ],
                'large' => [
                    'width' => 1440,
                    'height' => 810,
                    'crop' => ['center', 'center'],
                ],
            ]
        );

        $validated = \Vilare\Core\Validation::validate(
            $sizes,
            [
                'thumbnail' => 'required',
                'medium' => 'required',
                'large' => 'required',
                '*.width' => 'required|integer',
                '*.height' => 'required|integer',
                '*.crop' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (! is_bool($value) && ! is_array($value)) {
                            $fail('The ' . $attribute . ' must be an boolean or array.');
                        }
                    },
                ],
            ]
        );

        if (is_wp_error($validated)) {
            throw new \Exception(esc_attr($validated->get_error_message()));
        }

        if (! empty($sizes['thumbnail'])) {
            $this->default['thumbnail'] = $sizes['thumbnail'];
            unset($sizes['thumbnail']);
        }

        if (! empty($sizes['medium'])) {
            $this->default['medium'] = $sizes['medium'];
            unset($sizes['medium']);
        }

        if (! empty($sizes['large'])) {
            $this->default['large'] = $sizes['large'];
            unset($sizes['large']);
        }

        if (! empty($sizes)) {
            $this->additional = $sizes;
        }
    }

    private function isAllowedSize(string $size): bool
    {
        return ! empty($this->default[$size]) || ! empty($this->additional[$size]);
    }

    /**
     * @action after_setup_theme
     */
    public function setAdditionalSizes(): void
    {
        if (! empty($this->additional)) {
            foreach ($this->additional as $name => $data) {
                add_image_size($name, $data['width'], $data['height'], $data['crop']);
            }
        }
    }

    /**
     * @filter intermediate_image_sizes_advanced 10
     */
    public function filterDefaultSizes(array $sizes): array
    {
        if (! empty($this->default)) {
            foreach ($this->default as $name => $data) {
                if (! empty($sizes[$name])) {
                    $sizes[$name] = $data;
                }
            }
        }

        return $sizes;
    }

    /**
     * @action after_setup_theme
     */
    public function setDefaultSizes(): void
    {
        if (! empty($this->default)) {
            foreach ($this->default as $name => $data) {
                $width = (int) get_option("{$name}_size_w");
                $height = (int) get_option("{$name}_size_h");

                if (! empty($width) && $width !== $data['width']) {
                    update_option("{$name}_size_w", $data['width']);
                }

                if (! empty($height) && $height !== $data['height']) {
                    update_option("{$name}_size_h", $data['height']);
                }
            }
        }
    }

    /**
     * @filter intermediate_image_sizes
     */
    public function unsetSizes(array $sizes): array
    {
        $total = count($sizes);

        for ($i = 0; $i < $total; $i++) {
            if (! $this->isAllowedSize($sizes[$i])) {
                unset($sizes[$i]);
            }
        }

        return $sizes;
    }

    /**
     * @action init
     */
    public function removeSizes(): void
    {
        foreach (get_intermediate_image_sizes() as $size) {
            if (! $this->isAllowedSize($size)) {
                remove_image_size($size);
            }
        }
    }
}
