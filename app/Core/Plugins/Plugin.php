<?php

namespace Vilare\Core\Plugins;

abstract class Plugin
{
    private string $id = '';

    private string $title = '';

    final public function getId(): string
    {
        if (empty($this->id)) {
            throw new \Exception('Plugin ID is missing.');
        }

        return $this->id;
    }

    final protected function setId(string $id): void
    {
        $this->id = $id;
    }

    final public function getTitle(): string
    {
        if (empty($this->id)) {
            throw new \Exception('Plugin title is missing.');
        }

        return $this->title;
    }

    final protected function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
