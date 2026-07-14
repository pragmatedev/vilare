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

    public function generate(string $template, array $data = [], bool $minify = false): string
    {
        $content = $this->provider->generate($template, $data);

        return ! empty($minify) ? $this->minify($content) : $content;
    }

    public function view(string $template, array $data = [])
    {
        return $this->provider->view($template, $data);
    }

    public function minify(string $html): string
    {
        $html = preg_replace('/>[\s]+</', '><', $html);
        $html = str_replace("\n", '', $html);
        $html = preg_replace('/\s+(?=[\w:-]+\s*=)/', ' ', $html);

        return preg_replace('/(["\'])\s+(\/?>)/', '$1$2', $html);
    }
}
