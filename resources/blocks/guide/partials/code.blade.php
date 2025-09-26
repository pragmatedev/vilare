<div
    class="block-guide__code"
    x-data="{ tab: 'html' }"
>
    <div class="block-guide__code-header">
        <button
            x-bind:class="{ '-active': tab === 'html' }"
            x-on:click="tab = 'html'"
        >
            {{ __('HTML', 'vilare') }}
        </button>
        <button
            x-bind:class="{ '-active': tab === 'schema' }"
            x-on:click="tab = 'schema'"
        >
            {{ __('Schema', 'vilare') }}
        </button>
    </div>

    <div
        class="block-guide__code-content"
        x-show="tab === 'html'"
    >
        <code><pre>{{ $html }}</pre></code>
    </div>

    <div
        class="block-guide__code-content"
        x-show="tab === 'schema'"
        x-cloak
    >
        <code><pre>{{ $schema }}</pre></code>
    </div>

    <button
        class="block-guide__code-copy"
        x-on:click="copy('{{ $html }}')"
    >
        {{ __('Copy', 'vilare') }}
    </button>
</div>
