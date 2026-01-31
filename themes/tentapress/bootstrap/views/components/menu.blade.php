@props([
    'location' => 'primary',
    'items' => null,
])

@php
    $menuItems = is_array($items) ? $items : (isset($tpMenus) ? $tpMenus->itemsForLocation($location) : []);
@endphp

<nav {{ $attributes->merge(['class' => 'd-flex flex-wrap align-items-center gap-3 small text-secondary']) }}>
    @if ($menuItems !== [])
        @foreach ($menuItems as $item)
            @php
                $url = (string) ($item['url'] ?? '#');
                $title = (string) ($item['title'] ?? 'Menu');
                $target = isset($item['target']) && is_string($item['target']) ? $item['target'] : null;
                $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            @endphp

            <div class="d-flex flex-column gap-1">
                <a
                    href="{{ $url }}"
                    class="link-secondary text-decoration-none"
                    @if ($target) target="{{ $target }}" rel="noopener" @endif>
                    {{ $title }}
                </a>
                @if ($children !== [])
                    <div class="d-flex flex-wrap gap-2 small text-secondary">
                        @foreach ($children as $child)
                            @php
                                $childUrl = (string) ($child['url'] ?? '#');
                                $childTitle = (string) ($child['title'] ?? 'Menu');
                                $childTarget = isset($child['target']) && is_string($child['target']) ? $child['target'] : null;
                            @endphp

                            <a
                                href="{{ $childUrl }}"
                                class="link-secondary text-decoration-none"
                                @if ($childTarget) target="{{ $childTarget }}" rel="noopener" @endif>
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
