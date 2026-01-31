<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Admin\Menu;

interface MenuBuilderContract
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public function build(mixed $user = null): array;
}
