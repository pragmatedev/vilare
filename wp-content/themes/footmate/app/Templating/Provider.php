<?php

namespace FM\Templating;

use FM\Templating\Directives;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\CompilerEngine;

class Provider
{
    private ?Factory $factory = null;

    public function __construct()
    {
        add_action('after_setup_theme', fn() => $this->init());
    }

    public function render(string $template, array $data = []): void
    {
        echo $this->generate($template, $data); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function generate(string $template, array $data = []): string
    {
        return $this->view($template, $data)->render();
    }

    public function view(string $template, array $data = []): View
    {
        return fm()->filesystem()->exists($template)
            ? $this->factory->file($template, $data)
            : $this->factory->make($template, $data);
    }

    private function init(): void
    {
        $compiler = new BladeCompiler(fm()->filesystem(), fm()->config()->get('cache.path'));
        $resolver = new EngineResolver();
        $finder = new FileViewFinder(fm()->filesystem(), [fm()->config()->get('views.path')]);
        $dispatcher = new Dispatcher();
        $directives = new Directives();

        $directives->register($compiler);
        $resolver->register('blade', fn() => new CompilerEngine($compiler));

        $finder->addNamespace('blocks', fm()->config()->get('blocks.path'));
        $finder->addNamespace('components', fm()->config()->get('components.path'));
        $finder->addNamespace('templates', fm()->config()->get('templates.path'));

        $this->factory = new Factory($resolver, $finder, $dispatcher);

        do_action('fm_templating_provider_init', $compiler, $finder);
    }
}
