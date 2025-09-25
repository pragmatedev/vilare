## Initialization

Here are the steps that should be performed when initializing a new WordPress project.

1.  Clone the starter repository: `git clone git@github.com:pragmatedev/vilare.git vilare.com`
2.  Prepare the local environment:
    1.  Create local server with PHP 8.3 and MySQL 8.4.
    2.  Create a new database for the project: `vilare.com`
    3.  Assign a new domain to the project with SSL: `https://vilare.test`
3.  In the terminal, go to the project root and run `./init.sh`.

🎥 https://youtu.be/38ppFXgkIRk

## Installation

Here are the steps that should be performed when initializing an ongoing WordPress project.

1.  Clone the project repository: `git clone git@github.com:pragmatedev/vilare.com.git`
2.  Prepare the local environment:
    1.  Create local server with PHP 8.3 and MySQL 8.4.
    2.  Create a new database for the project: `vilare.com`
    3.  Assign a new domain to the project with SSL: `https://vilare.test`
3.  In the terminal, go to the project root and run `./init.sh`.
4.  Download the `wp-content/uploads` to your local environment from the remote.

🎥 https://youtu.be/BP8Aw5ZMn1A

## Commands

The project uses [Vite](https://pragmate.dev/wordpress/vite/integration/) as a bundler and development server with [HMR and Hot Reload](https://pragmate.dev/wordpress/vite/integration/#_4-bundling-improves-development-experience) features.

- `yarn dev` - Starts the development server \[[🔗](https://pragmate.dev/wordpress/vite/integration/#_4-bundling-improves-development-experience)\].
- `yarn build` - Builds theme assets in production mode \[[🔗](https://pragmate.dev/wordpress/vite/integration/#_2-bundling-improves-application-performance)\] \[[🔗](https://pragmate.dev/wordpress/vite/integration/#_3-bundling-improves-old-browsers-support)\].
- `yarn lint` - Checks codebase for meeting defined coding standards.
- `yarn format` - Formats codebase to meet defined coding standards.
- `yarn translate` - Generates `lang.pot` file in the theme resources.
- `yarn vilare` - Runs theme CLI tools.

The project uses `vilare` CLI tool to enhance development operations.

- `yarn vilare --help` - Displays help for a CLI tool.
- `yarn vilare database` - Manages database operations.
- `yarn vilare component` - Manages components operations.
- `yarn vilare test` - Manages testing operations.
- `yarn vilare release` - Creates production package.
- `yarn vilare release deploy` - Deploys production package to staging server.

🎥 https://youtu.be/1CXuoyRYb98

## General

- The project uses [Object Oriented](https://pragmate.dev/wordpress/architecture/oop-vs-procedural/#object-oriented-approach-in-wordpress) approach.
- The project uses [Singleton](https://pragmate.dev/wordpress/architecture/singleton/) for managing modules.
- The project uses [DocHooks syntax](https://pragmate.dev/wordpress/dochooks/#what-are-dochooks) for handling filters and actions.
- The project uses [Laravel Blade](https://pragmate.dev/wordpress/blade/introduction/) for creating components HTML structure.
- The project uses [Alpine JS](https://alpinejs.dev/start-here) for creating user interactions.
- The project uses [SASS](https://sass-lang.com/documentation/) for creating user interfaces.
- The project defines [coding standards](https://pragmate.dev/environment/linting/) for [PHP](https://pragmate.dev/php/phpcs/), [SCSS](https://pragmate.dev/css/stylelint/), [JS](https://pragmate.dev/js/eslint/) that must be met.
- The project uses Gutenberg to build the visual layer with [ACF Blocks](https://www.advancedcustomfields.com/resources/blocks/).
- The project [should limit plugins usage](https://pragmate.dev/wordpress/do-you-need-plugins/#how-to-decide-about-plugins-usage) as much as possible.
- The project should [split the responsibilities](https://pragmate.dev/architecture/model-view-controller/) as much as possible.
- The project uses [GitFlow](https://danielkummer.github.io/git-flow-cheatsheet/) as a branching workflow.
- Server details, passwords and other sensitive data are stored in 1Password.

## Requirements

### Development

- Single components should be developed on the [/playground/](https://vilare.com/playground/) template.
- Before approaching Pull Requests, Lighthouse tests should be performed using the `yarn vilare test psi` command, which checks the `/playground/` page with the newly created component and highlights any issues that must be resolved. **Any problems directly related to the component must be addressed before proceeding to the next steps.**
- After approving Pull Requests, the staging server should be updated, and the new block should be added to the [/demo/](https://vilare.com/demo/) template that lists all the custom blocks, allowing the team to access them in one place.

### Code Review

- Each `feature` branch must be merged to `develop` branch only via the Pull Request.
- Each Pull Request must meet the following requirements before merging:
  - It must pass the coding standard checks defined in Github Actions / Bitbucket Pipelines.
  - It should include Toggl, Basecamp, Preview links in the description.
  - It should include screenshots from Lighthouse tests performed by `yarn vilare test psi` command.
    - There should be not not any accessibility issues related to the specific feature.
    - There should be not not any performance issues related to the specific feature.
  - It should have at least one approval from the project leader.
    - Pull requests must be assigned for review only when all the previous requirements are met.
  - After approving pull request by the project leader, developer takes care of the merging process.

## Components

The project is created using a [component-driven](https://pragmate.dev/architecture/component-driven-development/#what-are-the-5-rules-of-a-good-component) approach. It consists of [components](https://github.com/pragmatedev/vilare/tree/master/resources/components), [blocks](https://github.com/pragmatedev/vilare/tree/master/resources/blocks), and [templates](https://github.com/pragmatedev/vilare/tree/master/resources/templates). Although each of them serve different role they share a similar style.

### Component

Components are the smallest units used to build websites supported by [Laravel Blade](https://laravel.com/docs/12.x/blade#rendering-components). They are available throughout the website and should include only the most basic functionalities to maintain simplicity.

- Creation: `yarn vilare component create --type=button --id=button --title=Button`
- Usage: `<x-button />`

### Block

Blocks are larger units integrated with [ACF and Gutenberg](https://www.advancedcustomfields.com/resources/blocks/), used to define the layout of the website. They can include more complex functionalities and utilize granular components. When possible, blocks should use [InnerBlocks](https://www.advancedcustomfields.com/resources/acf-blocks-using-innerblocks-and-parent-child-relationships/) to provide flexibility for clients and should [utilize container queries](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_containment/Container_queries) instead of traditional media queries.

- Creation: `yarn vilare component create --type=block --id=newsletter --title=Newsletter`
- Usage: `{!! block('newsletter')->render(['title' => 'custom title']) !!}`

### Template

Templates are the largest components. Those defined in the theme are automatically resolved and rendered by WordPress.

- Creation: `yarn vilare component create --type=template --id=article --title=Article`
- Usage: Choose page template from WordPress admin panel.

### Example

1.  Block Structure:
    1.  [Controller](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/Base.php): Used for defining the [block schema](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/Base.php#L13-L17), [default data](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/Base.php#L18-L22) and any backend mechanisms.
    2.  [Template](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/template.blade.php): Used for displaying [the data](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/template.blade.php#L10) that has been [defined in the block](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/Base.php#L13-L17).
    3.  [Scripts](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/script.js): Used for defining user interactivity.
    4.  [Styles](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/style.scss): Used for defining visual layer.
    5.  [ACF Fields](https://github.com/pragmatedev/vilare/blob/master/resources/fields/group_67a362c847851.json): Block fields saved with [Local JSON](https://www.advancedcustomfields.com/resources/local-json/) implementing [block schema](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/Base.php#L13-L17) for Gutenberg.
2.  Block Creation
    1.  Use the `yarn vilare component create --type=block --id=newsletter --title=Newsletter` command in the theme's root. The command will create a new block and automatically load it into the system. You might need to restart your development server.
    2.  Define the [block schema](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/Base.php#L13-L17) and fill it with the [default values](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/Base.php#L18-L22) when needed. Block schema uses the [Laravel Validation](https://laravel.com/docs/10.x/validation#available-validation-rules) component, but if you're not familiar with this yet, please at least define the simplest typing schema to check if the passed is a [string](https://laravel.com/docs/12.x/validation#rule-string), [array](https://laravel.com/docs/12.x/validation#rule-array), [boolean](https://laravel.com/docs/12.x/validation#rule-boolean), or [required](https://laravel.com/docs/12.x/validation#rule-required).
    3.  Go to the ACF Field Groups, clone the `Block: Base` group, and adjust to the newly created block while keeping the same naming conventions. The field naming and validation rules should reflect the ones defined in the schema.
3.  Block Rendering
    1.  Use the `{!! block('newsletter')->render(['title' => 'custom title']) !!}` function in Blade templates. To fill the block with values, pass the data array to the `render` function based on the [defined schema](https://github.com/pragmatedev/vilare/blob/master/.vilare/templates/blocks/base/Base.php#L13-L17).
    2.  Choose the block in the Gutenberg and fill with the data.
