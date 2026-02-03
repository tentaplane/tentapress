@include('tp-theme::blocks.hero', [
    'block' => $block ?? null,
    'props' => $props ?? [],
    'type' => $type ?? 'blocks/hero',
    'variant' => 'split',
])
