<?php

namespace Vilare\Core\Media;

use Vilare\Core\Media\Sizes;
use Vilare\Core\Media\WEBP;

class Media
{
    public function __construct()
    {
        \Vilare\App::init(new Sizes());
        \Vilare\App::init(new WEBP());
    }
}
