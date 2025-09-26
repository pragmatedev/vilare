<button
    {{
        $attributes
            ->class([
                'component-button',
                "-style-{$style}",
            ])
            ->merge([
                'type' => $type,
                'x-data' => 'component_button',
                'aria-label' => $slot->isNotEmpty() ? $label : null,
            ])
    }}
>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        {{ $label }}
    @endif
</button>
