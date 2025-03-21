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
                selected
                required
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
        />

        <span>
            {{ __('Accept privacy policy', 'fm') }}
        </span>
    </label>

    <button type="submit">
        {{ __('Submit', 'fm') }}
    </button>
</form>
