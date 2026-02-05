<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Admin\Widget;

interface WidgetBuilderContract
{
    /**
     * Build renderable widgets for the given user.
     *
     * @return array<int, WidgetContract>
     */
    public function build(mixed $user = null): array;
}
