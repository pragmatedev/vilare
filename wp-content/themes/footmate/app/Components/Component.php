<?php

namespace FM\Components;

use FM\Core\Validation;
use Illuminate\View\Component as ComponentBase;

abstract class Component extends ComponentBase
{
    private string $id = '';

    private string $title = '';

    private array $data = [];

    private array $schema = [];

    private bool $primary = true;

    final public function render(array $data = [])
    {
        return fm()->templating()->view("components::{$this->getId()}.template", $this->parse($this->data));
    }

    final public function generate(array $data = []): string
    {
        ob_start();

        fm()->templating()->render("components::{$this->getId()}.template", $this->parse($data));

        return ob_get_clean();
    }

    final protected function parse(array $data): array
    {
        $data = array_replace_recursive($this->getData(), $data);
        $data = apply_filters("fm_components_{$this->getId()}_data", $data);

        if ($this->hasSchema() && ! is_admin()) {
            $result = Validation::validate($data, $this->getSchema());

            if (is_wp_error($result)) {
                throw new \Exception(
                    esc_attr(
                        sprintf(
                            '%s component data verification failed: %s',
                            $this->getTitle(),
                            $result->get_error_message()
                        )
                    )
                );
            }
        }

        return $data;
    }

    final public function enqueue(): void
    {
        fm()->assets()->enqueue(
            "components/{$this->getId()}/script.js",
            [
                'handle' => "component-{$this->getId()}-script",
                'deps' => ['script'],
            ]
        );
        fm()->assets()->enqueue(
            "components/{$this->getId()}/style.scss",
            [
                'handle' => "component-{$this->getId()}-style",
                'deps' => ['style'],
            ]
        );
    }

    final public function getId(): string
    {
        if (empty($this->id)) {
            throw new \Exception('Component ID is missing.');
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
            throw new \Exception('Component Title is missing.');
        }

        return $this->title;
    }

    final protected function setTitle(string $title): void
    {
        $this->title = $title;
    }

    final protected function getData(string $key = ''): mixed
    {
        if (! empty($key)) {
            return data_get($this->data, $key);
        }

        return $this->data;
    }

    final protected function setData(array $data): void
    {
        $this->data = $data;
    }

    final public function hasData(string $key = ''): bool
    {
        if (! empty($key)) {
            return ! empty($this->getData($key));
        }

        return ! empty($this->getData());
    }

    final protected function getSchema(): array
    {
        return $this->schema;
    }

    final protected function setSchema(array $schema): void
    {
        $this->schema = $schema;
    }

    final public function hasSchema(): bool
    {
        return ! empty($this->getSchema());
    }

    public function isPrimary(): bool
    {
        return ! empty($this->primary);
    }

    final public function setPrimary(bool $primary = true): void
    {
        $this->primary = $primary;
    }

    /**
     * @action wp_enqueue_scripts
     * @action admin_enqueue_scripts
     */
    final public function enqueuePrimary(): void
    {
        if ($this->isPrimary()) {
            $this->enqueue();
        }
    }
}
