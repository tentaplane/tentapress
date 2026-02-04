@props([
    'location' => 'primary',
    'items' => null,
])

@php
    $menuItems = is_array($items) ? $items : (isset($tpMenus) ? $tpMenus->itemsForLocation($location) : []);
@endphp

<nav {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-6 text-sm font-medium text-surface-600']) }}>
    @if ($menuItems !== [])
        @foreach ($menuItems as $item)
            @php
                $url = (string) ($item['url'] ?? '#');
                $title = (string) ($item['title'] ?? 'Menu');
                $target = isset($item['target']) && is_string($item['target']) ? $item['target'] : null;
                $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            @endphp

            <div class="flex flex-col gap-1">
                <a href="{{ $url }}" class="rounded-md px-1 py-0.5 transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" @if ($target) target="{{ $target }}" rel="noopener" @endif>
                    {{ $title }}
                </a>
                @if ($children !== [])
                    <div class="flex flex-wrap gap-3 text-xs text-surface-400">
                        @foreach ($children as $child)
                            @php
                                $childUrl = (string) ($child['url'] ?? '#');
                                $childTitle = (string) ($child['title'] ?? 'Menu');
                                $childTarget = isset($child['target']) && is_string($child['target']) ? $child['target'] : null;
                            @endphp

                            <a href="{{ $childUrl }}" class="transition-colors hover:text-surface-900" @if ($childTarget) target="{{ $childTarget }}" rel="noopener" @endif>
                                {{ $childTitle }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @else
        {{ $slot }}
    @endif
</nav>
