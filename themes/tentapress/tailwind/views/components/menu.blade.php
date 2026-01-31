@props([
    'location' => 'primary',
    'items' => null,
])

@php
    $menuItems = is_array($items) ? $items : (isset($tpMenus) ? $tpMenus->itemsForLocation($location) : []);
@endphp

<nav {{ $attributes->merge(['class' => 'flex gap-4 text-sm text-black/70']) }}>
    @if ($menuItems !== [])
        @foreach ($menuItems as $item)
            @php
                $url = (string) ($item['url'] ?? '#');
                $title = (string) ($item['title'] ?? 'Menu');
                $target = isset($item['target']) && is_string($item['target']) ? $item['target'] : null;
                $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            @endphp

            <div class="flex flex-col gap-1">
                <a href="{{ $url }}" class="hover:underline" @if ($target) target="{{ $target }}" rel="noopener" @endif>
                    {{ $title }}
                </a>
                @if ($children !== [])
                    <div class="flex flex-wrap gap-3 text-xs text-black/60">
                        @foreach ($children as $child)
                            @php
                                $childUrl = (string) ($child['url'] ?? '#');
                                $childTitle = (string) ($child['title'] ?? 'Menu');
                                $childTarget = isset($child['target']) && is_string($child['target']) ? $child['target'] : null;
                            @endphp

                            <a href="{{ $childUrl }}" class="hover:text-black" @if ($childTarget) target="{{ $childTarget }}" rel="noopener" @endif>
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
