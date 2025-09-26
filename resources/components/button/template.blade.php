<button
    {{
        $attributes->class(['component-button'])->merge([
            'type' => $type,
            'class' => join(' ', ["-size-{$size}", "-style-{$style}"]),
            'x-data' => 'component_button',
        ])
    }}
>
    {{ $label }}
</button>
