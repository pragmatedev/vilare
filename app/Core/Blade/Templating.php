<?php

namespace Vilare\Core\Blade;

use Vilare\Core\Blade\Provider;
use Vilare\Core\Blade\Resolver;

class Templating
{
    private Provider $provider;

    public function __construct()
    {
        \Vilare\App::init(new Resolver());
        $this->provider = \Vilare\App::init(new Provider());
    }

    public function render(string $template, array $data = []): void
    {
        $this->provider->render($template, $data);
    }

    public function generate(string $template, array $data = []): string
    {
        return $this->provider->generate($template, $data);
    }

    public function view(string $template, array $data = [])
    {
        return $this->provider->view($template, $data);
    }
}
