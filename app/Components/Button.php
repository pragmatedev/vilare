<?php
namespace Vilare\Components;

use Vilare\Core\Components\Component;

class Button extends Component
{
    public function __construct(
        string $label = 'Label',
        string $type = 'button',
        string $size = 'lg',
        string $style = 'solid'
    )
    {
        $this->setId('button');
        $this->setTitle('Button');
        $this->setSchema(
            [
                'label' => 'required|string',
                'type' => 'required|string|in:button,reset,submit',
                'size' => 'required|string|in:sm,md,lg,xl,xxl',
                'style' => 'required|string|in:solid,outline',
            ]
        );
        $this->setData(
            [
                'label' => $label,
                'type' => $type,
                'size' => $size,
                'style' => $style,
            ]
        );
    }
}
