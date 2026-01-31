<?php

declare(strict_types=1);

namespace TentaPress\Import\Http\Admin;

final class IndexController
{
    public function __invoke()
    {
        return view('tentapress-import::index');
    }
}
