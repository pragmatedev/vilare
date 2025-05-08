<?php

namespace FM\Integrations;

trait ACFInnerBlocks
{
    private array $innerBlocks = [
        'active' => false,
        'list' => '',
    ];

    public function useInnerBlocks(): void
    {
        $this->innerBlocks['active'] = true;
    }

    public function usesInnerBlocks(): bool
    {
        return ! empty($this->innerBlocks['active']);
    }

    public function useInnerBlocksList(array $blocks): void
    {
        $this->innerBlocks['list'] = esc_attr(wp_json_encode($blocks));
    }

    public function getInnerBlocks(): string
    {
        $arguments = [];

        if (! empty($this->innerBlocks['list'])) {
            $arguments[] = sprintf('allowedBlocks="%s"', $this->innerBlocks['list']);
        }

        return sprintf('<InnerBlocks %s/>', join(' ', $arguments));
    }
}
