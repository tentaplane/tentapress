<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Admin\Widget;

abstract class AbstractWidget implements WidgetContract
{
    protected string $id = '';
    protected string $title = '';
    protected int $priority = 50;
    protected ?string $capability = null;
    protected int $colspan = 1;

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function capability(): ?string
    {
        return $this->capability;
    }

    public function colspan(): int
    {
        return max(1, min(3, $this->colspan));
    }

    public function canRender(): bool
    {
        return true;
    }

    /**
     * Configure widget properties from manifest definition.
     *
     * @param  array<string, mixed>  $config
     */
    public function configure(array $config): void
    {
        if (isset($config['id']) && is_string($config['id'])) {
            $this->id = $config['id'];
        }

        if (isset($config['title']) && is_string($config['title'])) {
            $this->title = $config['title'];
        }

        if (isset($config['position']) && is_numeric($config['position'])) {
            $this->priority = (int) $config['position'];
        }

        if (isset($config['capability'])) {
            $cap = trim((string) $config['capability']);
            $this->capability = $cap !== '' ? $cap : null;
        }

        if (isset($config['colspan']) && is_numeric($config['colspan'])) {
            $this->colspan = (int) $config['colspan'];
        }
    }

    /**
     * Safely render a Blade view, returning empty string on failure.
     *
     * @param  array<string, mixed>  $data
     */
    protected function view(string $view, array $data = []): string
    {
        try {
            return view($view, $data)->render();
        } catch (\Throwable) {
            return '';
        }
    }
}
