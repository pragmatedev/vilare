<?php

namespace Vilare\Blocks;

use Vilare\Core\Blocks\Block;

class Base extends Block
{
    public function __construct()
    {
        $this->setId('base');
        $this->setTitle('Base');
        $this->setSchema(
            [
                'title' => 'required|string',
            ]
        );
        $this->setData(
            [
                'title' => 'Base',
            ]
        );
    }
}
