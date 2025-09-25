<?php
namespace Vilare\Components;

use Vilare\Core\Components\Component;

class Base extends Component
{
    public function __construct(string $title = '')
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
                'title' => $title,
            ]
        );
    }
}
