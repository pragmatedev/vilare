<div
    {{
        $attributes
            ->class(['block-guide'])
            ->merge([
                'x-data' => 'block_guide',
            ])
    }}
>
    @include(
        'blocks::guide.partials.header',
        [
            'id' => 'typography',
            'title' => __('Typography', 'vilare'),
        ]
    )

    <div
        class="block-guide__table"
        style="--cols: 1"
    >
        @foreach (['text-h1', 'text-h2', 'text-h3', 'text-h4', 'text-h5', 'text-h6'] as $item)
            <div class="block-guide__row">
                <div class="-{{ $item }} -font-primary">
                    {{ $title }}
                </div>

                <x-button
                    label=".{{ $item }}"
                    size="md"
                    style="outline"
                    x-on:click="copy('@extend %{{ $item }};')"
                />
            </div>
        @endforeach
    </div>

    <div
        class="block-guide__table"
        style="--cols: 1"
    >
        @foreach (['font-primary', 'font-secondary'] as $item)
            <div class="block-guide__row">
                <div class="-{{ $item }}">
                    {{ $title }}
                </div>

                <x-button
                    label=".{{ $item }}"
                    size="md"
                    style="outline"
                    x-on:click="copy('@extend %{{ $item }};')"
                />
            </div>
        @endforeach
    </div>

    <div
        class="block-guide__table"
        style="--cols: 1"
    >
        @foreach (['text-xl', 'text-lg', 'text-md', 'text-sm', 'text-xs'] as $item)
            <div class="block-guide__col">
                <div class="-{{ $item }} -font-secondary">
                    {{ $description }}
                </div>

                <x-button
                    label=".{{ $item }}"
                    size="md"
                    style="outline"
                    x-on:click="copy('@extend %{{ $item }};')"
                />
            </div>
        @endforeach
    </div>

    @include(
        'blocks::guide.partials.header',
        [
            'id' => 'colors',
            'title' => __('Colors', 'vilare'),
        ]
    )

    <div
        class="block-guide__table"
        style="--cols: 5"
    >
        @foreach (['primary-50', 'primary-100', 'primary-200', 'primary-300', 'primary-400', 'primary-500', 'primary-600', 'primary-700', 'primary-800', 'primary-900'] as $item)
            <div class="block-guide__col">
                <div
                    style="
                        background-color: var(--color-{{ $item }});
                        width: 100%;
                        height: 5rem;
                        cursor: pointer;
                        border-radius: var(--radius-md);
                    "
                    x-on:click="copy('var(--color-{{ $item }})')"
                ></div>

                <x-button
                    label="{{ $item }}"
                    size="md"
                    style="outline"
                    x-on:click="copy('var(--color-{{ $item }})')"
                />
            </div>
        @endforeach
    </div>

    @include(
        'blocks::guide.partials.header',
        [
            'id' => 'radiuses',
            'title' => __('Radiuses', 'vilare'),
        ]
    )

    <div
        class="block-guide__table"
        style="--cols: 6"
    >
        @foreach (['radius-sm', 'radius-md', 'radius-lg', 'radius-xl', 'radius-xxl', 'radius-full'] as $item)
            <div class="block-guide__col">
                <div
                    class="-{{ $item }}"
                    style="
                        background-color: var(--color-primary-100);
                        width: 100%;
                        height: 5rem;
                        cursor: pointer;
                    "
                    x-on:click="copy('var(--color-{{ $item }})')"
                ></div>

                <x-button
                    label=".{{ $item }}"
                    size="md"
                    style="outline"
                    x-on:click="copy('@extend %{{ $item }};')"
                />
            </div>
        @endforeach
    </div>

    @include(
        'blocks::guide.partials.header',
        [
            'id' => 'buttons',
            'title' => __('Buttons', 'vilare'),
        ]
    )

    @include(
        'blocks::guide.partials.code',
        [
            'html' => component('button')->dd('html'),
            'schema' => component('button')->dd('schema'),
        ]
    )

    <div class="block-guide__table">
        <div class="block-guide__col">
            <x-button
                label="sm | solid"
                size="sm"
                style="solid"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="md | solid"
                size="md"
                style="solid"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="lg | solid"
                size="lg"
                style="solid"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="xl | solid"
                size="xl"
                style="solid"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="xxl | solid"
                size="xxl"
                style="solid"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="sm | outline"
                size="sm"
                style="outline"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="md | outline"
                size="md"
                style="outline"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="lg | outline"
                size="lg"
                style="outline"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="xl | outline"
                size="xl"
                style="outline"
            />
        </div>

        <div class="block-guide__col">
            <x-button
                label="xxl | outline"
                size="xxl"
                style="outline"
            />
        </div>
    </div>

    @include(
        'blocks::guide.partials.header',
        [
            'id' => 'containers',
            'title' => __('Containers', 'vilare'),
        ]
    )

    <div class="block-guide__table -container-full">
        @foreach (['container-full', 'container-xxl', 'container-xl', 'container-lg', 'container-md', 'container-sm'] as $item)
            <div
                class="block-guide__row -{{ $item }}"
                style="
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 100%;
                    min-height: 5rem;
                    padding: 1rem;
                    background: var(--color-primary-50);
                "
            >
                <x-button
                    label=".{{ $item }}"
                    size="md"
                    style="solid"
                    x-on:click="copy('@extend %{{ $item }};')"
                />
            </div>
        @endforeach
    </div>

    @include('blocks::guide.partials.tooltip')
</div>
