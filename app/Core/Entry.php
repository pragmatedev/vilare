<?php

namespace Vilare\Core;

use WP_Post;
use DateTimeImmutable;

abstract class Entry
{
    private WP_Post $entity;

    private int $id;

    private string $slug;

    private string $title;

    private string $description;

    private int $thumbnail;

    private string $url;

    private DateTimeImmutable $date;

    public function __construct(int|WP_Post $post)
    {
        if (is_numeric($post)) {
            $post = get_post($post);
        }

        if (empty($post)) {
            throw new \Exception('This entry could not be resolved.');
        }

        $this->setEntity($post);
        $this->setId($post->ID);
    }

    public function getEntity(): WP_Post
    {
        return $this->entity;
    }

    private function setEntity(WP_Post $entity): void
    {
        $this->entity = $entity;
    }

    public function hasEntity(): bool
    {
        return true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    private function setId(int $id): void
    {
        $this->id = $id;
    }

    public function hasId(): bool
    {
        return ! empty($this->getId());
    }

    public function getSlug(): string
    {
        if (! isset($this->slug)) {
            $this->setSlug(get_post_field('post_name', $this->getId()));
        }

        return $this->slug;
    }

    protected function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function hasSlug(): bool
    {
        return ! empty($this->getSlug());
    }

    public function getTitle(): string
    {
        if (! isset($this->title)) {
            $this->setTitle(get_the_title($this->getId()));
        }

        return $this->title;
    }

    protected function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function hasTitle(): bool
    {
        return ! empty($this->getTitle());
    }

    public function getDescription(): string
    {
        if (! isset($this->description)) {
            $this->setDescription(get_the_excerpt($this->getId()));
        }

        return $this->description;
    }

    protected function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function hasDescription(): bool
    {
        return ! empty($this->getDescription());
    }

    public function getThumbnail(): int
    {
        if (! isset($this->thumbnail)) {
            $this->setThumbnail(get_post_thumbnail_id($this->getId()));
        }

        return $this->thumbnail;
    }

    protected function setThumbnail(int $id): void
    {
        $this->thumbnail = $id;
    }

    public function hasThumbnail(): bool
    {
        return ! empty($this->getThumbnail());
    }

    public function getUrl(): string
    {
        if (! isset($this->url)) {
            $this->setUrl(get_the_permalink($this->getId()));
        }

        return $this->url;
    }

    protected function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function hasUrl(): bool
    {
        return ! empty($this->getUrl());
    }

    public function getDate(): DateTimeImmutable
    {
        if (! isset($this->date)) {
            $this->setDate(new DateTimeImmutable(get_the_date('c', $this->getId())));
        }

        return $this->date;
    }

    private function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function hasDate(): bool
    {
        return true;
    }

    public function getTerms(string $taxonomy): array
    {
        return get_the_terms($this->getId(), $taxonomy);
    }

    public function getMeta(string $key, mixed $fallback = null): mixed
    {
        $value = get_field($key, $this->getId());

        return ! is_null($value) ? $value : $fallback;
    }

    public function hasMeta(string $key): bool
    {
        return ! is_null($this->getMeta($key));
    }
}
