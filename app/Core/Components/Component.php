<?php

namespace Vilare\Core\Components;

use Vilare\Core\Validation;
use Vilare\Core\Blade\TemplatingException;
use Illuminate\View\Component as ComponentBase;
use Illuminate\View\ComponentAttributeBag;

abstract class Component extends ComponentBase
{
    private string $id = '';

    private string $title = '';

    private array $data = [];

    private array $schema = [];

    private array $dependencies = [];

    private bool $primary = true;

    final public function render(array $data = [])
    {
        return vilare()->templating()->view("components::{$this->getId()}.template", $this->parse($this->data));
    }

    final public function generate(array $data = []): string
    {
        $data['attributes'] = $this->newAttributeBag();

        try {
            return vilare()->templating()->generate("components::{$this->getId()}.template", $this->parse($data));
        } catch (TemplatingException $th) {
            return "<div>⚠️ Block {$this->getTitle()} Exception: {$th->getMessage()}</div>";
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    final protected function parse(array $data): array
    {
        $data = array_replace_recursive($this->getData(), $data);
        $data = apply_filters("vilare_components_{$this->getId()}_data", $data);

        if ($this->hasSchema()) {
            $result = Validation::validate($data, $this->getSchema());

            if (is_wp_error($result)) {
                throw new TemplatingException(esc_attr($result->get_error_message()));
            }
        }

        if (empty($data['attributes'])) {
            $data['attributes'] = new ComponentAttributeBag([]);
        }

        return $data;
    }

    final public function dd($mode = 'html'): string
    {
        switch ($mode) {
            case 'schema':
                return implode(
                    "\r\n",
                    array_map(
                        fn($key, $value) => sprintf('%s="%s"', $key, $value),
                        array_keys($this->getSchema()),
                        $this->getSchema()
                    )
                );

            default:
                return sprintf(
                    '<x-%s %s />',
                    $this->getId(),
                    implode(
                        ' ',
                        array_map(
                            fn($key, $value) => sprintf('%s="%s"', $key, $value),
                            array_keys($this->data),
                            $this->data
                        )
                    )
                );
        }
    }

    final public function enqueue(): void
    {
        vilare()->assets()->enqueue(
            "components/{$this->getId()}/script.js",
            [
                'handle' => "component-{$this->getId()}-script",
                'deps' => ['script'],
            ]
        );

        vilare()->assets()->enqueue(
            "components/{$this->getId()}/style.scss",
            [
                'handle' => "component-{$this->getId()}-style",
                'deps' => ['style'],
            ]
        );

        if (in_array('swiper', $this->dependencies)) {
            vilare()->assets()->enqueue('scripts/swiper.js', ['handle' => 'swiper']);
            vilare()->assets()->enqueue('styles/swiper.scss', ['handle' => 'swiper']);
        }
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

    final public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
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
