<?php

declare(strict_types=1);

namespace TentaPress\Menus\Services;

use TentaPress\System\Theme\ThemeManager;

final readonly class ThemeMenuLocations
{
    public function __construct(
        private ThemeManager $themes,
    ) {
    }

    /**
     * @return array<int,array{key:string,label:string}>
     */
    public function all(): array
    {
        $active = $this->themes->activeTheme();
        if (! is_array($active)) {
            return [];
        }

        $manifest = $active['manifest'] ?? null;
        if (! is_array($manifest)) {
            return [];
        }

        $raw = $manifest['menu_locations'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $locations = [];

        foreach ($raw as $key => $label) {
            $locationKey = trim(is_string($key) ? $key : '');
            if ($locationKey === '') {
                continue;
            }

            $locationLabel = trim(is_string($label) ? $label : '');
            if ($locationLabel === '') {
                $locationLabel = ucfirst($locationKey);
            }

            $locations[] = [
                'key' => $locationKey,
                'label' => $locationLabel,
            ];
        }

        usort(
            $locations,
            static fn (array $a, array $b): int => strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? '')),
        );

        return $locations;
    }

    /**
     * @return array<int,string>
     */
    public function keys(): array
    {
        return array_map(static fn (array $loc): string => (string) ($loc['key'] ?? ''), $this->all());
    }
}
