<div
    {{
        $attributes
            ->class(['block-guide'])
            ->merge([
                'x-data' => 'block_guide',
            ])
    }}
>
    <h2
        id="typography"
        class="block-guide__header"
    >
        <a href="#typography">
            {{ __('Typography', 'vilare') }}
        </a>
    </h2>

    <div class="block-guide__table">
        @foreach (['text-h1', 'text-h2', 'text-h3', 'text-h4', 'text-h5', 'text-h6'] as $item)
            <div class="block-guide__row -text">
                <div class="-{{ $item }}">What is Lorem Ipsum?</div>
                <button x-on:click="copy('@extend %{{ $item }};')">
                    .{{ $item }}
                </button>
            </div>
        @endforeach

        @foreach (['text-xl', 'text-lg', 'text-md', 'text-sm', 'text-xs'] as $item)
            <div class="block-guide__row -text">
                <div class="-{{ $item }}">
                    Lorem Ipsum is simply dummy text of the printing and
                    industry. Lorem Ipsum has been the industry's standard dummy
                    text ever since the 1500s, when an unknown printer took a
                    galley of type and scrambled it to make.
                </div>
                <button x-on:click="copy('@extend %{{ $item }};')">
                    .{{ $item }}
                </button>
            </div>
        @endforeach
    </div>

    <h2
        id="fonts"
        class="block-guide__header"
    >
        <a href="#fonts">
            {{ __('Fonts', 'vilare') }}
        </a>
    </h2>

    <div class="block-guide__table">
        @foreach (['font-primary', 'font-secondary'] as $item)
            <div class="block-guide__row -font">
                <div class="-{{ $item }}">What is Lorem Ipsum?</div>
                <button x-on:click="copy('@extend %{{ $item }};')">
                    .{{ $item }}
                </button>
            </div>
        @endforeach
    </div>

    <h2
        id="colors"
        class="block-guide__header"
    >
        <a href="#colors">
            {{ __('Colors', 'vilare') }}
        </a>
    </h2>

    <div class="block-guide__table">
        @foreach (['gray-50', 'gray-100', 'gray-200', 'gray-300', 'gray-400', 'gray-500', 'gray-600', 'gray-700', 'gray-800', 'gray-900', 'gray-950'] as $item)
            <div class="block-guide__row -color">
                <div style="background-color: var(--color-{{ $item }})"></div>
                <button x-on:click="copy('var(--color-{{ $item }})')">
                    var(--color-{{ $item }})
                </button>
            </div>
        @endforeach
    </div>

    <h2
        id="radiuses"
        class="block-guide__header"
    >
        <a href="#radiuses">
            {{ __('Radiuses', 'vilare') }}
        </a>
    </h2>

    <div class="block-guide__table">
        @foreach (['radius-full', 'radius-xxl', 'radius-xl', 'radius-lg', 'radius-md', 'radius-sm'] as $item)
            <div class="block-guide__row -radius">
                <div class="-{{ $item }}"></div>
                <button x-on:click="copy('@extend %{{ $item }};')">
                    .{{ $item }}
                </button>
            </div>
        @endforeach
    </div>

    <h2
        id="containers"
        class="block-guide__header"
    >
        <a href="#containers">
            {{ __('Containers', 'vilare') }}
        </a>
    </h2>

    <div class="block-guide__table -container-full">
        @foreach (['container-full', 'container-xxl', 'container-xl', 'container-lg', 'container-md', 'container-sm'] as $item)
            <div class="block-guide__row -container -{{ $item }}">
                <button x-on:click="copy('@extend %{{ $item }};')">
                    .{{ $item }}
                </button>
            </div>
        @endforeach
    </div>

    <div
        class="block-guide__tooltip"
        x-show="tooltip"
        x-transition.opacity
        x-cloak
    >
        {{ __('Copied', 'vilare') }}
    </div>
</div>
