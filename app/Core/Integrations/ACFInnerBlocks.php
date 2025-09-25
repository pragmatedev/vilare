<?php

namespace Vilare\Core\Integrations;

trait ACFInnerBlocks
{
    private array $innerBlocks = [
        'active' => false,
        'list' => '',
        'template' => '',
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

    public function useInnerBlocksTemplate(array $template): void
    {
        $this->innerBlocks['template'] = esc_attr(wp_json_encode($template));
    }

    public function getInnerBlocks(): string
    {
        $arguments = [];

        if (! empty($this->innerBlocks['list'])) {
            $arguments[] = sprintf('allowedBlocks="%s"', $this->innerBlocks['list']);
        }

        if (! empty($this->innerBlocks['template'])) {
            $arguments[] = sprintf('template="%s"', $this->innerBlocks['template']);
        }

        return sprintf('<InnerBlocks %s/>', join(' ', $arguments));
    }
}
