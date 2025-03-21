<?php

namespace FM\Blocks;

use FM\Blocks\Block;

class Contact extends Block
{
    public function __construct()
    {
        $this->setId('contact');
        $this->setTitle('Contact');
        $this->setSchema(
            [
                'title' => 'required|string|max:60',
            ]
        );
        $this->setData(
            [
                'title' => 'Contact',
            ]
        );
    }
}
