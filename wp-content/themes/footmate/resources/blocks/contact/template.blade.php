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
            value="First User"
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
            value="first@local.test"
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
        />
    </fieldset>

    <fieldset>
        <label for="type">
            {{ __('Type', 'fm') }}
        </label>

        <select
            name="type"
            id="type"
        >
            <option
                value=""
                disabled
                required
            >
                {{ __('Select', 'fm') }}
            </option>

            <option
                value="email"
                selected
            >
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
            checked
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

    <button type="submit">
        {{ __('Submit', 'fm') }}
    </button>
</form>
