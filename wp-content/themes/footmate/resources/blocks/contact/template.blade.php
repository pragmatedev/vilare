<form
    method="POST"
    action="{{ admin_url('admin-post.php') }}"
    class="contact"
>
    <h2>
        {{ __('Contact Us', 'fm') }}
    </h2>

    <fieldset>
        <label for="name">
            {{ __('Name', 'fm') }}
        </label>

        <input
            type="text"
            name="name"
            id="name"
            required
            autocomplete="off"
        />
    </fieldset>

    <fieldset>
        <label for="email">
            {{ __('Email', 'fm') }}
        </label>

        <input
            type="email"
            name="email"
            id="email"
            required
            autocomplete="off"
        />
    </fieldset>

    <fieldset>
        <label for="phone">
            {{ __('Phone', 'fm') }}
        </label>

        <input
            type="phone"
            name="phone"
            id="phone"
            autocomplete="off"
        />
    </fieldset>

    <fieldset>
        <label for="type">
            {{ __('Type', 'fm') }}
        </label>

        <select
            name="type"
            id="type"
            required
        >
            <option
                value=""
                disabled
                selected
                autocomplete="off"
            >
                {{ __('Select', 'fm') }}
            </option>

            <option value="email">
                {{ __('Email', 'fm') }}
            </option>

            <option value="phone">
                {{ __('Phone', 'fm') }}
            </option>
        </select>
    </fieldset>

    <label for="privacy">
        <input
            type="checkbox"
            id="privacy"
            name="privacy"
            required
            autocomplete="off"
        />

        <span>
            {{ __('Accept privacy policy', 'fm') }}
        </span>
    </label>

    <input
        type="hidden"
        name="action"
        value="contact"
    />
    <input
        type="hidden"
        name="nonce"
        value="{{ wp_create_nonce('contact') }}"
    />

    <button type="submit">
        {{ __('Submit', 'fm') }}
    </button>
</form>
