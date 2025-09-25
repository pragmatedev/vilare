<?php

namespace Vilare\Core;

use Vilare\App;
use Vilare\Core\Blade\Templating;
use Vilare\Core\Blocks\Blocks;
use Vilare\Core\Cache;
use Vilare\Core\Components\Components;
use Vilare\Core\Config;
use Vilare\Core\Debugger;
use Vilare\Core\Hooks;
use Vilare\Core\Integrations\Integrations;
use Vilare\Core\Media\Media;
use Vilare\Core\Templates\Templates;
use Illuminate\Filesystem\Filesystem;

abstract class Core
{
    private Blocks $blocks;

    private Cache $cache;

    private Components $components;

    private Config $config;

    private Filesystem $filesystem;

    private Integrations $integrations;

    private Media $media;

    private Templates $templates;

    private Templating $templating;

    private static ?App $instance = null;

    protected function __construct()
    {
        $this->blocks = App::init(new Blocks());
        $this->cache = App::init(new Cache());
        $this->components = App::init(new Components());
        $this->config = App::init(new Config());
        $this->filesystem = new Filesystem();
        $this->integrations = App::init(new Integrations());
        $this->media = App::init(new Media());
        $this->templates = App::init(new Templates());
        $this->templating = App::init(new Templating());

        if (! empty(WP_DEBUG)) {
            \Vilare\App::init(new Debugger());
        }
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }

    public static function get(): App
    {
        if (empty(self::$instance)) {
            self::$instance = new App();
        }

        return self::$instance;
    }

    public function blocks(): Blocks
    {
        return $this->blocks;
    }

    public function cache(): Cache
    {
        return $this->cache;
    }

    public function components(): Components
    {
        return $this->components;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function filesystem(): Filesystem
    {
        return $this->filesystem;
    }

    public function integrations(): Integrations
    {
        return $this->integrations;
    }

    public function media(): Media
    {
        return $this->media;
    }

    public function templates(): Templates
    {
        return $this->templates;
    }

    public function templating(): Templating
    {
        return $this->templating;
    }

    public static function init(object $module): object
    {
        return Hooks::init($module);
    }
}
