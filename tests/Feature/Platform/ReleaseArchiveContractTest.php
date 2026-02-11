<?php

declare(strict_types=1);

it('keeps test sources excluded from release archives', function (): void {
    $attributes = (string) file_get_contents(base_path('.gitattributes'));

    expect($attributes)->toContain('/tests export-ignore');
    expect($attributes)->toContain('/plugins/**/tests export-ignore');
    expect($attributes)->toContain('/packages/**/tests export-ignore');
});
