<?php

namespace Vilare\Blocks;

use Vilare\Core\Blocks\Block;

class Guide extends Block
{
    public function __construct()
    {
        $this->setId('guide');
        $this->setTitle('Guide');
        $this->setSchema([]);
        $this->setData([]);
    }
}
