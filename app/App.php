<?php

namespace Vilare;

use Vilare\Core\Core;
use Vilare\Assets;
use Vilare\Setup;

class App extends Core
{
    private Assets $assets;

    private Setup $setup;

    protected function __construct()
    {
        parent::__construct();
        $this->assets = self::init(new Assets());
        $this->setup = self::init(new Setup());
    }

    public function assets(): Assets
    {
        return $this->assets;
    }

    public function setup(): Setup
    {
        return $this->setup;
    }
}
