<?php

declare(strict_types=1);

it('keeps test sources excluded from release archives', function (): void {
    $attributes = (string) file_get_contents(base_path('.gitattributes'));
    $lines = preg_split('/\R/', $attributes) ?: [];

    expect($lines)->toContain('/tests export-ignore');
    expect($lines)->toContain('/plugins/**/tests export-ignore');
    expect($lines)->toContain('/packages/**/tests export-ignore');
});
