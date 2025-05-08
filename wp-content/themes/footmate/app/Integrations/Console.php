<?php
/**
 * W.I.P
 * @see https://github.com/roots/acorn/tree/main/src/Roots/Acorn/Console
 * @see https://make.wordpress.org/cli/handbook/references/internal-api/wp-cli-add-command/
 * phpcs:disable
 */
namespace FM\Integrations;

use WP_CLI;

class Console
{
    public function __construct()
    {
        WP_CLI::add_command(
            'fm release',
            [$this, 'release'],
            [
                'shortdesc' => 'Creates release version of the theme.',
            ]
        );

        WP_CLI::add_command(
            'fm block',
            [$this, 'block'],
            [
                'shortdesc' => 'Creates a new block of the theme.',
            ]
        );

        WP_CLI::add_command(
            'fm component',
            [$this, 'component'],
            [
                'shortdesc' => 'Creates a new component of the theme.',
            ]
        );

        WP_CLI::add_command(
            'fm rename',
            [$this, 'rename'],
            [
                'shortdesc' => 'Renames theme elements.',
            ]
        );
    }

    public function release(array $args = [], array $assoc = []): void
    {
        $paths = [
            'source' => FM_PATH,
            'output' => FM_PATH . '/.output',
        ];

        fm()->filesystem()->exists($paths['output']) ? fm()->filesystem()->cleanDirectory($paths['output']) : fm()->filesystem()->makeDirectory($paths['output']);

        shell_exec("cd {$paths['source']} && composer install --no-dev --quiet");

        fm()->filesystem()->copyDirectory($paths['source'] . '/vendor', $paths['output'] . '/vendor');

        shell_exec("cd {$paths['source']} && composer install --quiet");

        shell_exec("cd {$paths['source']} && yarn build");

        fm()->filesystem()->copyDirectory($paths['source'] . '/dist', $paths['output'] . '/dist');

        fm()->filesystem()->copyDirectory($paths['source'] . '/app', $paths['output'] . '/app');

        fm()->filesystem()->copyDirectory($paths['source'] . '/inc', $paths['output'] . '/inc');

        fm()->filesystem()->copyDirectory($paths['source'] . '/resources', $paths['output'] . '/resources');

        fm()->filesystem()->deleteDirectory($paths['output'] . '/resources/scripts');

        fm()->filesystem()->deleteDirectory($paths['output'] . '/resources/styles');

        fm()->filesystem()->deleteDirectory($paths['output'] . '/resources/fonts');

        fm()->filesystem()->deleteDirectory($paths['output'] . '/resources/images');

        collect(fm()->filesystem()->allFiles($paths['output'] . '/resources/blocks', true))
            ->filter(fn($file) => in_array($file->getExtension(), ['scss', 'js'], true))
            ->each(fn($file) => fm()->filesystem()->delete($file->getPathname()));

        collect(fm()->filesystem()->allFiles($paths['output'] . '/resources/templates', true))
            ->filter(fn($file) => in_array($file->getExtension(), ['scss', 'js'], true))
            ->each(fn($file) => fm()->filesystem()->delete($file->getPathname()));

        collect(fm()->filesystem()->allFiles($paths['output'] . '/resources/components', true))
            ->filter(fn($file) => in_array($file->getExtension(), ['scss', 'js'], true))
            ->each(fn($file) => fm()->filesystem()->delete($file->getPathname()));

        collect(fm()->filesystem()->allFiles($paths['output'], true))
            ->filter(fn($file) => in_array($file->getExtension(), ['gitkeep', 'DS_Store'], true))
            ->each(fn($file) => fm()->filesystem()->delete($file->getPathname()));

        if (! empty($assoc['zip'])) {
            fm()->filesystem()->copyDirectory($paths['output'], FM_PATH . '/footmate');
            shell_exec("cd {$paths['source']} && zip -r footmate.zip footmate");
            fm()->filesystem()->cleanDirectory($paths['output']);
            fm()->filesystem()->move(FM_PATH . '/footmate.zip', $paths['output'] . '/footmate.zip');
            fm()->filesystem()->deleteDirectory(FM_PATH . '/footmate');
        }

        WP_CLI::success('🚀 Release complete.');
    }

    public function block(array $args = [], array $assoc = []): void
    {
        if (empty($assoc['id'])) {
            WP_CLI::error('Block ID is required.');
        }

        if (empty($assoc['title'])) {
            WP_CLI::error('Block Title is required.');
        }

        if (!preg_match('/^[a-z\-]+$/', $assoc['id'])) {
            WP_CLI::error('Block ID has incorrect format.');
        }

        if (!preg_match('/^[a-zA-Z]+$/', $assoc['title'])) {
            WP_CLI::error('Block Title has incorrect format.');
        }

        if (fm()->filesystem()->exists(FM_PATH . '/app/Blocks/' . $assoc['title'] . '.php')) {
            WP_CLI::error('Block already exists.');
        }

        if (fm()->filesystem()->exists(FM_PATH . '/resources/blocks/' . $assoc['id'])) {
            WP_CLI::error('Block already exists.');
        }

        fm()->filesystem()->copy(FM_PATH . '/app/Blocks/Base.php', FM_PATH . '/app/Blocks/' . $assoc['title'] . '.php');
        fm()->filesystem()->copyDirectory(FM_PATH . '/resources/blocks/base', FM_PATH . '/resources/blocks/' . $assoc['id']);

        $files = [
            FM_PATH . '/app/Blocks/' . $assoc['title'] . '.php',
            FM_PATH . '/resources/blocks/' . $assoc['id'] . '/template.blade.php',
            FM_PATH . '/resources/blocks/' . $assoc['id'] . '/script.js',
            FM_PATH . '/resources/blocks/' . $assoc['id'] . '/style.scss',
        ];

        foreach ($files as $file) {
            $content = fm()->filesystem()->get($file);
            $content = str_replace('Base', $assoc['title'], $content);
            $content = str_replace('base', $assoc['id'], $content);
            fm()->filesystem()->put($file, $content);
        }

        WP_CLI::success('🚀 Block created.');
    }

    public function component(array $args = [], array $assoc = []): void
    {
        if (empty($assoc['id'])) {
            WP_CLI::error('Component ID is required.');
        }

        if (empty($assoc['title'])) {
            WP_CLI::error('Component Title is required.');
        }

        if (!preg_match('/^[a-z\-]+$/', $assoc['id'])) {
            WP_CLI::error('Component ID has incorrect format.');
        }

        if (!preg_match('/^[a-zA-Z]+$/', $assoc['title'])) {
            WP_CLI::error('Component Title has incorrect format.');
        }

        if (fm()->filesystem()->exists(FM_PATH . '/app/Components/' . $assoc['title'] . '.php')) {
            WP_CLI::error('Component already exists.');
        }

        if (fm()->filesystem()->exists(FM_PATH . '/resources/components/' . $assoc['id'])) {
            WP_CLI::error('Component already exists.');
        }

        fm()->filesystem()->copy(FM_PATH . '/app/Components/Base.php', FM_PATH . '/app/Components/' . $assoc['title'] . '.php');
        fm()->filesystem()->copyDirectory(FM_PATH . '/resources/components/base', FM_PATH . '/resources/components/' . $assoc['id']);

        $files = [
            FM_PATH . '/app/Components/' . $assoc['title'] . '.php',
            FM_PATH . '/resources/components/' . $assoc['id'] . '/template.blade.php',
            FM_PATH . '/resources/components/' . $assoc['id'] . '/script.js',
            FM_PATH . '/resources/components/' . $assoc['id'] . '/style.scss',
        ];

        foreach ($files as $file) {
            $content = fm()->filesystem()->get($file);
            $content = str_replace('Base', $assoc['title'], $content);
            $content = str_replace('base', $assoc['id'], $content);
            fm()->filesystem()->put($file, $content);
        }

        WP_CLI::success('🚀 Component created.');
    }

    public function rename(array $args = [], array $assoc = []): void
    {
        if (empty($assoc['company'])) {
            WP_CLI::error('--company attribute is required');
        }

        if (empty($assoc['name'])) {
            WP_CLI::error('--name attribute is required');
        }

        if (empty($assoc['slug'])) {
            WP_CLI::error('--slug attribute is required');
        }

        if (empty($assoc['namespace'])) {
            WP_CLI::error('--namespace attribute is required');
        }

        if (empty($assoc['initials'])) {
            WP_CLI::error('--initials attribute is required');
        }

        if (empty($assoc['domain'])) {
            WP_CLI::error('--domain attribute is required');
        }

        fm()->filesystem()->delete(FM_PATH . '/composer.lock');
        fm()->filesystem()->delete(FM_PATH . '/yarn.lock');

        $ignore = ['dist', 'node_modules', 'vendor', '.output', '.DS_Store', 'images', 'fonts', 'screenshot.png', 'yarn.lock', 'composer.lock'];

        $directories = collect(fm()->filesystem()->directories(FM_PATH))->filter(fn($directory) => ! in_array(fm()->filesystem()->basename($directory), $ignore, true));
        $directories->push(FM_PATH . '/.husky');
        $directories->push(FM_PATH . '/.vite');

        $files = collect(fm()->filesystem()->files(FM_PATH, true));

        foreach ($directories as $directory) {
            $files = $files->merge(fm()->filesystem()->allFiles($directory, true));
        }

        $files = $files
            ->filter(fn($file) => ! in_array(fm()->filesystem()->basename($file), $ignore, true))
            ->map(fn($file) => $file->getPathname())
            ->filter(fn($file) => preg_match('/' . implode('|', array_map('preg_quote', $ignore)) . '/', $file) !== 1);

        $files->push(FM_PATH . '/phpcs.xml.dist');

        foreach ($files as $file) {
            fm()->filesystem()->put($file, str_replace(['fm.tentyp.test', 'tentypdev', 'FootMATE', 'footmate', 'FM', 'fm'], [$assoc['domain'], $assoc['company'], $assoc['name'], $assoc['slug'], $assoc['namespace'], $assoc['initials']], fm()->filesystem()->get($file)));
        }

        WP_CLI::success('Theme renamed. Run `composer install`, `yarn build`, `composer analyze` to test the results.');
    }
}
