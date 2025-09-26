<?php

namespace Vilare\Blocks;

use Vilare\Core\Blocks\Block;

class Guide extends Block
{
    public function __construct()
    {
        $this->setId('guide');
        $this->setTitle('Guide');
        $this->setSchema(
            [
                'title' => 'required|string|max:60',
                'description' => 'required|string|max:255',
            ]
        );
        $this->setData(
            [
                'title' => 'What is Lorem Ipsum?',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make.', // phpcs:ignore Generic.Files.LineLength.TooLong
            ]
        );
    }
}
