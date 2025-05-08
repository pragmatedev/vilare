<?php

namespace FM;

use FM\Assets\Assets;
use FM\Blocks\Blocks;
use FM\Cache;
use FM\Components\Components;
use FM\Core\Config;
use FM\Core\Hooks;
use FM\Integrations\Integrations;
use FM\Setup;
use FM\Templates\Templates;
use FM\Templating\Templating;
use Illuminate\Filesystem\Filesystem;

class App
{
    private Assets $assets;

    private Blocks $blocks;

    private Cache $cache;

    private Components $components;

    private Config $config;

    private Filesystem $filesystem;

    private Integrations $integrations;

    private Setup $setup;

    private Templates $templates;

    private Templating $templating;


    private static ?App $instance = null;

    private function __construct()
    {
        $this->assets = self::init(new Assets());
        $this->blocks = self::init(new Blocks());
        $this->cache = self::init(new Cache());
        $this->components = self::init(new Components());
        $this->config = self::init(new Config());
        $this->filesystem = new Filesystem();
        $this->integrations = self::init(new Integrations());
        $this->setup = self::init(new Setup());
        $this->templates = self::init(new Templates());
        $this->templating = self::init(new Templating());
    }

    public function assets(): Assets
    {
        return $this->assets;
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

    public function setup(): Setup
    {
        return $this->setup;
    }

    public function templates(): Templates
    {
        return $this->templates;
    }

    public function templating(): Templating
    {
        return $this->templating;
    }

    private function __clone()
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

    public static function init(object $module): object
    {
        return Hooks::init($module);
    }
}
