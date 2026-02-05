<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Admin\Widget;

interface WidgetContract
{
    /**
     * Unique identifier: vendor/plugin:widget-id
     */
    public function id(): string;

    /**
     * Widget display title.
     */
    public function title(): string;

    /**
     * Sort order (lower = earlier). Default: 50.
     */
    public function priority(): int;

    /**
     * Required capability, or null for all users.
     */
    public function capability(): ?string;

    /**
     * Grid column span: 1, 2, or 3 (out of 3).
     */
    public function colspan(): int;

    /**
     * Whether this widget can render (data available, plugin enabled, etc.).
     * Called before render() to allow hiding without overhead.
     */
    public function canRender(): bool;

    /**
     * Render the widget HTML. Must be defensive - never throw.
     * Return empty string to hide widget entirely.
     */
    public function render(): string;
}
