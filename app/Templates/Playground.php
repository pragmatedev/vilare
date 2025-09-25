<?php

namespace Vilare\Templates;

use Vilare\Core\Templates\Template;

class Playground extends Template
{
    public function __construct()
    {
        $this->setId('playground');
        $this->setTitle('Playground');
        $this->setSchema([]);
        $this->setData([]);
    }

    /**
     * @action show_admin_bar
     */
    public function hideAdminBar(bool $show): bool
    {
        return $this->isCurrent() ? false : $show;
    }
}
