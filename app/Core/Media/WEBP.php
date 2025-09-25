<?php

namespace Vilare\Core\Media;

use WebPConvert\WebPConvert;

class WEBP
{
    private bool $enabled = false;

    private array $mimes = [
        'image/jpeg',
        'image/png',
    ];

    public function __construct()
    {
        if (defined('VILARE_ENABLE_WEBP_CONVERSION') && ! empty(VILARE_ENABLE_WEBP_CONVERSION)) {
            $this->enabled = true;
        }
    }

    private function convert(string $path): void
    {
        if (! empty($path) && $this->shouldGenerate($path)) {
            WebPConvert::convert($path, $this->getWebpPath($path), []);
        }
    }

    /**
     * @filter wp_handle_upload 10
     */
    public function handleUpload(array $file): array
    {
        $this->convert($file['file']);

        return $file;
    }

    /**
     * @filter image_make_intermediate_size 10
     */
    public function handleResizing(string $name): string
    {
        $this->convert($name);

        return $name;
    }

    /**
     * @filter wp_delete_file 10
     */
    public function handleDeleting(string $path): string
    {
        if ($this->shouldRemove($path)) {
            wp_delete_file($this->getWebpPath($path));
        }

        return $path;
    }

    private function getWebpPath(string $path): string
    {
        $pathInfo = pathinfo($path);

        return str_replace(
            "{$pathInfo['filename']}.{$pathInfo['extension']}",
            "{$pathInfo['filename']}.webp",
            $path
        );
    }

    private function shouldGenerate(string $filePath): bool
    {
        return $this->isActive() && $this->isAllowedMimeType($filePath) && file_exists($filePath);
    }

    private function shouldRemove(string $filePath): bool
    {
        if ($this->isActive() && $this->isAllowedMimeType($filePath)) {
            $webpPath = $this->getWebpPath($filePath);

            return ! empty($webpPath) && file_exists($webpPath);
        }

        return false;
    }

    private function isAllowedMimeType(string $filePath): bool
    {
        return in_array(mime_content_type($filePath), $this->mimes);
    }

    private function isActive(): bool
    {
        return true === $this->enabled;
    }
}
