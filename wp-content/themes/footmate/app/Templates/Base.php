<?php

namespace FM\Templates;

use FM\Templates\Template;

class Base extends Template
{
    public function __construct()
    {
        $this->setId('base');
        $this->setTitle('Base');
        $this->setSchema([]);
        $this->setData([]);
    }
}
