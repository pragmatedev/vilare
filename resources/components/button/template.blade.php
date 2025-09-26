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
            ])
    }}
>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @endif

    {{ $label }}
</button>
