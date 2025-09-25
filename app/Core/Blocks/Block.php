<?php

namespace Vilare\Core\Blocks;

use Vilare\Core\Validation;
use Vilare\Core\Integrations\ACFInnerBlocks;
use Vilare\Core\Blade\TemplatingException;
use Illuminate\View\ComponentAttributeBag;

abstract class Block
{
    use ACFInnerBlocks;

    private string $id = '';

    private string $title = '';

    private array $data = [];

    private array $schema = [];

    private array $dependencies = [];

    private bool $primary = false;

    final public function render(array $data = []): void
    {
        $this->enqueue();
        echo $this->generate($data); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    final public function generate(array $data = []): string
    {
        try {
            return vilare()->templating()->generate("blocks::{$this->getId()}.template", $this->parse($data));
        } catch (TemplatingException $th) {
            return "<div>⚠️ Block {$this->getTitle()} Exception: {$th->getMessage()}</div>";
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    final protected function parse(array $data): array
    {
        $data = array_replace_recursive($this->getData(), $data);
        $data = apply_filters("vilare_blocks_{$this->getId()}_data", $data);

        if ($this->hasSchema()) {
            $result = Validation::validate($data, $this->getSchema());

            if (is_wp_error($result)) {
                throw new TemplatingException(esc_attr($result->get_error_message()));
            }
        }

        $attributes = new ComponentAttributeBag();

        if (! empty($data['attributes']['id'])) {
            $attributes = $attributes->merge(['id' => $data['attributes']['id']]);
        }

        if (! empty($data['attributes']['class'])) {
            $attributes = $attributes->merge(['class' => $data['attributes']['class']]);
        }

        $data['attributes'] = $attributes;

        return $data;
    }

    final public function enqueue(): void
    {
        vilare()->assets()->enqueue(
            "blocks/{$this->getId()}/script.js",
            [
                'handle' => "block-{$this->getId()}-script",
                'deps' => ['script'],
            ]
        );

        vilare()->assets()->enqueue(
            "blocks/{$this->getId()}/style.scss",
            [
                'handle' => "block-{$this->getId()}-style",
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
            throw new \Exception('Block ID is missing.');
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
            throw new \Exception('Block Title is missing.');
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
